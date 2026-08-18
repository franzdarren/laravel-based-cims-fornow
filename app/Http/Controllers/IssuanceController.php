<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\IssuanceTransaction;
use App\Models\TransactionLog;
use App\Services\FefoAllocator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IssuanceController extends Controller
{
    protected const DRAFT_KEY = 'issuance_draft';

    public function index()
    {
        $issuances = IssuanceTransaction::with(['transaction.user', 'transaction.lines.batch.item'])
            ->whereHas('transaction')
            ->get()
            ->sortByDesc(fn ($i) => $i->date)
            ->take(30)
            ->values();

        return view('issuance.index', compact('issuances'));
    }

    public function create(Request $request)
    {
        $items = InventoryItem::active()->where('item_category', '!=', 'EQUIPMENT')->orderBy('item_name')->get()
            ->filter(fn (InventoryItem $i) => $i->stockOnHand() > 0)->values();
        $draft = $request->session()->get(self::DRAFT_KEY, ['header' => [], 'lines' => []]);

        return view('issuance.create', [
            'items' => $items,
            'header' => $draft['header'],
            'lines' => $draft['lines'],
        ]);
    }

    // Live FEFO allocation preview used by the create-issuance form's meter.
    public function allocationPreview(Request $request)
    {
        $data = $request->validate([
            'item_id' => ['nullable', 'exists:inventory_items,item_id'],
            'quantity' => ['nullable', 'integer'],
        ]);

        $item = ! empty($data['item_id']) ? InventoryItem::find($data['item_id']) : null;
        $qty = max(0, (int) ($data['quantity'] ?? 0));

        if (! $item) {
            return response()->json(['rows' => [], 'short' => 0, 'available' => 0, 'unit' => '']);
        }

        $result = FefoAllocator::previewWithShortfall($item, $qty);

        return response()->json([
            'rows' => $result['allocations']->map(fn ($a) => [
                'batch_number' => $a['batch']->batch_no,
                'expiry_date' => optional($a['batch']->expiry_date)->format('d M Y'),
                'qty' => $a['qty'],
            ])->all(),
            'short' => $result['short'],
            'available' => $item->stockOnHand(),
            'unit' => $item->uom?->uom_name,
        ]);
    }

    public function addLine(Request $request)
    {
        $header = $request->validate([
            'employee_no' => ['required', 'string', 'max:50'],
            'employee_name' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'employee_supervisor' => ['nullable', 'string', 'max:100'],
            'disposition' => ['required', 'in:Returned to work,Sent home,Referred to hospital'],
            'chief_complaint' => ['required', 'string', 'max:150'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $line = $request->validate([
            'item_id' => ['required', 'exists:inventory_items,item_id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = InventoryItem::findOrFail($line['item_id']);

        try {
            $allocation = FefoAllocator::preview($item, (int) $line['quantity']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        $line['item_name'] = $item->item_name;
        $line['unit_of_measure'] = $item->uom?->uom_name;
        $line['allocation_preview'] = $allocation->map(fn ($a) => [
            'batch_number' => $a['batch']->batch_no,
            'expiry_date' => optional($a['batch']->expiry_date)->format('Y-m-d'),
            'qty' => $a['qty'],
        ])->all();

        $draft = $request->session()->get(self::DRAFT_KEY, ['header' => [], 'lines' => []]);
        $draft['header'] = $header;
        $draft['lines'][] = $line;
        $request->session()->put(self::DRAFT_KEY, $draft);

        return redirect()->route('issuance.create')->with('status', 'Item added to issuance using FEFO.');
    }

    public function removeLine(Request $request, int $index)
    {
        $draft = $request->session()->get(self::DRAFT_KEY, ['header' => [], 'lines' => []]);
        unset($draft['lines'][$index]);
        $draft['lines'] = array_values($draft['lines']);
        $request->session()->put(self::DRAFT_KEY, $draft);

        return redirect()->route('issuance.create');
    }

    public function clearDraft(Request $request)
    {
        $request->session()->forget(self::DRAFT_KEY);

        return redirect()->route('issuance.create');
    }

    public function store(Request $request)
    {
        $draft = $request->session()->get(self::DRAFT_KEY, ['header' => [], 'lines' => []]);

        if (empty($draft['lines'])) {
            return back()->withErrors(['lines' => 'Add at least one item before recording the issuance.']);
        }

        $header = validator($draft['header'], [
            'employee_no' => ['required', 'string', 'max:50'],
            'employee_name' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'employee_supervisor' => ['nullable', 'string', 'max:100'],
            'disposition' => ['required', 'in:Returned to work,Sent home,Referred to hospital'],
            'chief_complaint' => ['required', 'string', 'max:150'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $log = TransactionLog::create([
            'transaction_type' => 'ISSUANCE',
            'user_id' => $request->user()->user_id,
            'reference_no' => IssuanceTransaction::nextReferenceNo(),
        ]);

        $issuance = IssuanceTransaction::create([
            ...$header,
            'transaction_id' => $log->transaction_id,
        ]);

        foreach ($draft['lines'] as $line) {
            $item = InventoryItem::findOrFail($line['item_id']);

            try {
                $allocations = FefoAllocator::allocateAndDeduct($item, (int) $line['quantity']);
            } catch (RuntimeException $e) {
                return redirect()->route('issuance.create')->withErrors([
                    'quantity' => "Could not complete issuance: {$e->getMessage()} Please review and re-add this item.",
                ]);
            }

            foreach ($allocations as $allocation) {
                $batch = $allocation['batch'];
                $before = $batch->quantity_on_hand + $allocation['qty'];
                $log->lines()->create([
                    'batch_id' => $batch->batch_id,
                    'qty_before' => $before,
                    'qty_after' => $batch->quantity_on_hand,
                    'quantity_issued' => $allocation['qty'],
                    'status_before' => 'ACTIVE',
                    'status_after' => $batch->batch_status,
                    'line_remarks' => $item->item_category === 'MEDICINE'
                        ? "{$item->item_name} (batch {$batch->batch_no}): stock {$before} → {$batch->quantity_on_hand} {$item->uom?->uom_name}; issued {$allocation['qty']} {$item->uom?->uom_name}"
                        : "{$item->item_name} (batch {$batch->batch_no}): issued {$allocation['qty']} {$item->uom?->uom_name}",
                ]);
            }
        }

        $request->session()->forget(self::DRAFT_KEY);

        return redirect()->route('issuance.index')->with('status', "Issuance {$log->reference_no} recorded.");
    }

    /**
     * Edit a POSTED issuance: header fields plus per-line quantity changes
     * (0 removes the line), reconciling batch on-hand for every batch
     * touched — including several lines sharing the same batch.
     */
    public function update(Request $request, IssuanceTransaction $issuance)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'employee_no' => ['required', 'string', 'max:50'],
            'employee_name' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'employee_supervisor' => ['nullable', 'string', 'max:100'],
            'chief_complaint' => ['required', 'string', 'max:150'],
            'disposition' => ['required', 'in:Returned to work,Sent home,Referred to hospital'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'lines' => ['required', 'array'],
            'lines.*' => ['required', 'integer', 'min:0'],
        ]);

        $log = $issuance->transaction;
        $originalLines = $log->lines()->with('batch.item')->get();

        $originalByBatch = [];
        foreach ($originalLines as $line) {
            $originalByBatch[$line->batch_id] = ($originalByBatch[$line->batch_id] ?? 0) + (int) $line->quantity_issued;
        }

        $newQtyByLine = [];
        $newByBatch = [];
        foreach ($data['lines'] as $lineId => $qty) {
            $line = $originalLines->firstWhere('line_id', (int) $lineId);
            if (! $line) {
                continue;
            }
            $qty = (int) $qty;
            $newQtyByLine[$line->line_id] = $qty;
            if ($qty > 0) {
                $newByBatch[$line->batch_id] = ($newByBatch[$line->batch_id] ?? 0) + $qty;
            }
        }

        if (empty($newByBatch)) {
            return back()->withErrors(['lines' => 'An issuance must contain at least one issued item.']);
        }

        try {
            DB::transaction(function () use ($originalLines, $originalByBatch, $newByBatch, $newQtyByLine, $issuance, $log, $data) {
                $batchIds = array_unique(array_merge(array_keys($originalByBatch), array_keys($newByBatch)));

                foreach ($batchIds as $batchId) {
                    $batch = Batch::find($batchId);
                    if (! $batch) {
                        throw new RuntimeException('One of the original batches is no longer available.');
                    }
                    $before = (int) $batch->quantity_on_hand;
                    $oldQty = (int) ($originalByBatch[$batchId] ?? 0);
                    $newQty = (int) ($newByBatch[$batchId] ?? 0);
                    $capacity = $before + $oldQty;

                    if ($newQty > $capacity) {
                        throw new RuntimeException("Insufficient stock in batch {$batch->batch_no}.");
                    }

                    $after = $capacity - $newQty;
                    $batch->quantity_on_hand = $after;
                    $batch->batch_status = $after === 0 ? 'INACTIVE' : 'ACTIVE';
                    $batch->save();

                    $item = $batch->item;
                    $lineForBatch = $originalLines->firstWhere('batch_id', $batchId);
                    if ($lineForBatch) {
                        $lineForBatch->update([
                            'qty_before' => $before,
                            'qty_after' => $after,
                            'quantity_issued' => $newQtyByLine[$lineForBatch->line_id] ?? 0,
                            'status_after' => $batch->batch_status,
                            'line_remarks' => $item->item_category === 'MEDICINE'
                                ? "{$item->item_name} (batch {$batch->batch_no}): stock {$before} → {$after} {$item->uom?->uom_name}; issued {$oldQty} → {$newQty} {$item->uom?->uom_name}"
                                : "{$item->item_name} (batch {$batch->batch_no}): issued {$oldQty} → {$newQty} {$item->uom?->uom_name}",
                        ]);
                    }
                }

                foreach ($originalLines as $line) {
                    $qty = $newQtyByLine[$line->line_id] ?? 0;
                    if ($qty <= 0) {
                        $line->delete();
                    }
                }

                $issuance->update([
                    'employee_no' => $data['employee_no'],
                    'employee_name' => $data['employee_name'],
                    'department' => $data['department'] ?? null,
                    'employee_supervisor' => $data['employee_supervisor'] ?? null,
                    'chief_complaint' => $data['chief_complaint'],
                    'disposition' => $data['disposition'],
                    'remarks' => $data['remarks'] ?? null,
                ]);

                $log->update(['transaction_datetime' => $data['date']]);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['lines' => $e->getMessage()]);
        }

        return redirect()->route('issuance.index')->with('status', 'Issuance record updated.');
    }
}
