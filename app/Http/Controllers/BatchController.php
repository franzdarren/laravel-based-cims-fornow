<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\TransactionLog;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::with('item')->orderBy('expiry_date');

        if ($status = $request->query('status')) {
            $query->where('batch_status', strtoupper($status));
        }

        $batches = $query->get();

        return view('batches.index', compact('batches'));
    }

    // Nurse only — enforced by route middleware.
    public function edit(Batch $batch)
    {
        return view('batches.edit', compact('batch'));
    }

    public function update(Request $request, Batch $batch)
    {
        $data = $request->validate([
            'batch_number' => ['required', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'qty_on_hand' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $before = $batch->quantity_on_hand;

        $batch->update([
            'batch_no' => $data['batch_number'],
            'brand' => $data['brand'],
            'expiry_date' => $data['expiry_date'],
            'quantity_on_hand' => $data['qty_on_hand'],
            'batch_status' => $data['qty_on_hand'] > 0 ? 'ACTIVE' : 'INACTIVE',
        ]);

        $log = TransactionLog::create([
            'transaction_type' => 'ADJUSTMENT',
            'user_id' => auth()->id(),
            'reference_no' => $batch->batch_no,
            'reason' => $data['reason'],
        ]);
        $log->lines()->create([
            'batch_id' => $batch->batch_id,
            'qty_before' => $before,
            'qty_after' => $data['qty_on_hand'],
            'status_before' => 'ACTIVE',
            'status_after' => $batch->batch_status,
            'line_remarks' => "Adjusted {$batch->item->item_name} (batch {$batch->batch_no}): stock {$before} → {$data['qty_on_hand']} {$batch->item->uom?->uom_name}. Reason: {$data['reason']}",
        ]);

        return redirect()->route('batches.index')->with('status', 'Batch updated.');
    }

    public function dispose(Request $request, Batch $batch)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$batch->quantity_on_hand],
            'reason' => ['required', 'in:Expired,Damaged,Contaminated,Packaging issue,Other'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $batch->quantity_on_hand;
        $batch->quantity_on_hand -= $data['quantity'];
        $batch->batch_status = $batch->quantity_on_hand <= 0 ? 'DISPOSED' : 'ACTIVE';
        if ($batch->quantity_on_hand < 0) {
            $batch->quantity_on_hand = 0;
        }
        $batch->save();

        $log = TransactionLog::create([
            'transaction_type' => 'DISPOSAL',
            'user_id' => $request->user()->user_id,
            'reference_no' => TransactionLog::nextDisposalRef(),
            'reason' => $data['reason'].($data['remarks'] ? " — {$data['remarks']}" : ''),
        ]);
        $log->lines()->create([
            'batch_id' => $batch->batch_id,
            'qty_before' => $before,
            'qty_after' => $batch->quantity_on_hand,
            'status_before' => 'ACTIVE',
            'status_after' => $batch->batch_status,
            'line_remarks' => "Disposed {$data['quantity']} {$batch->item->uom?->uom_name} of {$batch->item->item_name} from batch {$batch->batch_no}",
        ]);

        return redirect()->route('batches.index')->with('status', "Disposal {$log->reference_no} recorded.");
    }
}
