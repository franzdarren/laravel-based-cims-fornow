<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\ReceivingTransaction;
use App\Models\ReceivingTransactionLine;
use App\Models\Supplier;
use App\Models\TransactionLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReceivingController extends Controller
{
    protected const DRAFT_KEY = 'receiving_draft';

    public function index(Request $request)
    {
        $user = $request->user();

        $myPending = ReceivingTransaction::with('supplier')
            ->where('received_by', $user->user_id)
            ->where('status', 'PENDING')
            ->latest('date_received')
            ->get();

        $myReturned = ReceivingTransaction::with(['supplier', 'lines.item'])
            ->where('received_by', $user->user_id)
            ->where('status', 'RETURNED')
            ->latest('decided_at')
            ->get();

        $recent = ReceivingTransaction::with(['supplier', 'receivedBy', 'approvedBy'])
            ->where('received_by', $user->user_id)
            ->whereNotIn('status', ['PENDING', 'RETURNED'])
            ->latest('date_received')
            ->limit(30)
            ->get();

        return view('receiving.index', compact('myPending', 'myReturned', 'recent'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::active()->orderBy('supplier_name')->get();
        $items = InventoryItem::active()->orderBy('item_name')->get();
        $locations = SettingController::locationList();
        $draft = $request->session()->get(self::DRAFT_KEY, ['header' => [], 'lines' => []]);

        return view('receiving.create', [
            'suppliers' => $suppliers,
            'items' => $items,
            'locations' => $locations,
            'header' => $draft['header'],
            'lines' => $draft['lines'],
        ]);
    }

    public function addLine(Request $request)
    {
        $header = $request->validate([
            'supplier_id' => ['required', 'exists:supplier,supplier_id'],
            'reference_no' => ['required', 'string', 'max:100'],
            'date_received' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:150'],
        ]);

        $item = InventoryItem::findOrFail($request->input('item_id'));

        $lineRules = [
            'item_id' => ['required', 'exists:inventory_items,item_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'brand' => ['nullable', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:100'],
        ];

        if ($item->item_category === 'EQUIPMENT') {
            $lineRules['quantity'] = ['required', 'integer', 'in:1'];
            $lineRules['model'] = ['nullable', 'string', 'max:100'];
            $lineRules['serial_number'] = ['required', 'string', 'max:100'];
            $lineRules['asset_tag'] = ['required', 'string', 'max:100'];
        } else {
            $lineRules['batch_number'] = ['nullable', 'string', 'max:100'];
            $lineRules['expiry_date'] = ['nullable', 'date'];
        }

        $line = $request->validate($lineRules);

        $draft = $request->session()->get(self::DRAFT_KEY, ['header' => [], 'lines' => []]);

        if ($item->item_category === 'EQUIPMENT') {
            $tag = strtolower($line['asset_tag']);
            $takenInSystem = Equipment::whereRaw('LOWER(asset_tag) = ?', [$tag])->exists();
            $takenInDraft = collect($draft['lines'])->contains(fn ($l) => strtolower($l['asset_tag'] ?? '') === $tag);
            if ($takenInSystem || $takenInDraft) {
                throw ValidationException::withMessages(['asset_tag' => 'Asset tag already exists.']);
            }
        }

        $line['item_name'] = $item->item_name;
        $line['category'] = $item->item_category;

        $draft['header'] = $header;
        $draft['lines'][] = $line;
        $request->session()->put(self::DRAFT_KEY, $draft);

        return redirect()->route('receiving.create')->with('status', 'Delivery line added.');
    }

    public function removeLine(Request $request, int $index)
    {
        $draft = $request->session()->get(self::DRAFT_KEY, ['header' => [], 'lines' => []]);
        unset($draft['lines'][$index]);
        $draft['lines'] = array_values($draft['lines']);
        $request->session()->put(self::DRAFT_KEY, $draft);

        return redirect()->route('receiving.create');
    }

    public function clearDraft(Request $request)
    {
        $request->session()->forget(self::DRAFT_KEY);

        return redirect()->route('receiving.create');
    }

    public function store(Request $request)
    {
        $draft = $request->session()->get(self::DRAFT_KEY, ['header' => [], 'lines' => []]);

        if (empty($draft['lines'])) {
            return back()->withErrors(['lines' => 'Add at least one delivery line before submitting.']);
        }

        $header = validator($draft['header'], [
            'supplier_id' => ['required', 'exists:supplier,supplier_id'],
            'reference_no' => ['required', 'string', 'max:100', Rule::unique('receiving_transaction', 'ref_no')],
            'date_received' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:150'],
        ])->validate();

        $receiving = ReceivingTransaction::create([
            'ref_no' => $header['reference_no'],
            'supplier_id' => $header['supplier_id'],
            'date_received' => $header['date_received'],
            'remarks' => $header['remarks'] ?? null,
            'received_by' => $request->user()->user_id,
            'status' => 'PENDING',
        ]);

        foreach ($draft['lines'] as $line) {
            $locationId = $this->locationIdFor($line['location'] ?? null);

            $receiving->lines()->create([
                'item_id' => $line['item_id'],
                'quantity' => $line['quantity'],
                'brand' => $line['brand'] ?? null,
                'batch_no' => $line['batch_number'] ?? null,
                'expiry_date' => $line['expiry_date'] ?? null,
                'model' => $line['model'] ?? null,
                'serial_number' => $line['serial_number'] ?? null,
                'asset_tag' => $line['asset_tag'] ?? null,
                'location_id' => $locationId,
            ]);
        }

        $itemNames = $receiving->lines()->with('item')->get()->map(fn ($l) => $l->item->item_name.' × '.$l->quantity)->implode('; ');
        TransactionLog::note($request->user(), "Submitted receiving transaction for supervisor approval. Items: {$itemNames}", $receiving->ref_no);

        $request->session()->forget(self::DRAFT_KEY);

        return redirect()->route('receiving.index')->with('status', "Receiving transaction {$receiving->ref_no} submitted for approval.");
    }

    public function cancel(Request $request, ReceivingTransaction $receiving)
    {
        abort_unless($receiving->received_by === $request->user()->user_id, 403);
        abort_unless($receiving->isPending(), 422, 'Only pending requests can be cancelled.');

        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $receiving->cancel($request->user(), $data['reason']);

        return redirect()->route('receiving.index')->with('status', 'Request cancelled.');
    }

    // ------------------------------------------------------------------
    // Returned-request editing
    // ------------------------------------------------------------------

    public function updateReturnedDetails(Request $request, ReceivingTransaction $receiving)
    {
        $this->authorizeOwnReturned($request, $receiving);

        $data = $request->validate([
            'supplier_id' => ['required', 'exists:supplier,supplier_id'],
            'reference_no' => ['required', 'string', 'max:100', Rule::unique('receiving_transaction', 'ref_no')->ignore($receiving->receiving_transaction_id, 'receiving_transaction_id')],
            'date_received' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:150'],
        ]);

        $receiving->update([
            'supplier_id' => $data['supplier_id'],
            'ref_no' => $data['reference_no'],
            'date_received' => $data['date_received'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        TransactionLog::note($request->user(), 'Updated returned receiving transaction details.', $receiving->ref_no);

        return redirect()->route('receiving.index')->with('status', 'Receiving details updated.');
    }

    public function updateReturnedLine(Request $request, ReceivingTransaction $receiving, ReceivingTransactionLine $line)
    {
        $this->authorizeOwnReturned($request, $receiving);
        abort_unless($line->receiving_transaction_id === $receiving->receiving_transaction_id, 404);

        $item = $line->item;

        $rules = [
            'quantity' => ['required', 'integer', 'min:1'],
            'brand' => ['nullable', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:100'],
        ];
        if ($item->item_category === 'EQUIPMENT') {
            $rules['quantity'] = ['required', 'integer', 'in:1'];
            $rules['model'] = ['nullable', 'string', 'max:100'];
            $rules['serial_number'] = ['required', 'string', 'max:100'];
            $rules['asset_tag'] = ['required', 'string', 'max:100'];
        } else {
            $rules['batch_number'] = ['nullable', 'string', 'max:100'];
            $rules['expiry_date'] = ['nullable', 'date'];
        }

        $data = $request->validate($rules);
        $locationId = $this->locationIdFor($data['location']);

        if ($item->item_category === 'EQUIPMENT') {
            $tag = strtolower($data['asset_tag']);
            $taken = Equipment::whereRaw('LOWER(asset_tag) = ?', [$tag])->exists()
                || ReceivingTransactionLine::where('receiving_transaction_line_id', '!=', $line->receiving_transaction_line_id)
                    ->where('receiving_transaction_id', $receiving->receiving_transaction_id)
                    ->whereRaw('LOWER(asset_tag) = ?', [$tag])->exists();
            if ($taken) {
                throw ValidationException::withMessages(['asset_tag' => 'Asset tag already exists.']);
            }

            $line->update([
                'quantity' => $data['quantity'], 'brand' => $data['brand'] ?? null, 'location_id' => $locationId,
                'model' => $data['model'] ?? null, 'serial_number' => $data['serial_number'], 'asset_tag' => $data['asset_tag'],
                'batch_no' => null, 'expiry_date' => null,
            ]);
        } else {
            $line->update([
                'quantity' => $data['quantity'], 'brand' => $data['brand'] ?? null, 'location_id' => $locationId,
                'batch_no' => $data['batch_number'] ?? null, 'expiry_date' => $data['expiry_date'] ?? null,
                'model' => null, 'serial_number' => null, 'asset_tag' => null,
            ]);
        }

        TransactionLog::note($request->user(), "Updated returned receiving item {$item->item_name}.", $receiving->ref_no);

        return redirect()->route('receiving.index')->with('status', 'Returned item updated.');
    }

    public function resubmitReturned(Request $request, ReceivingTransaction $receiving)
    {
        $this->authorizeOwnReturned($request, $receiving);

        $lines = $receiving->lines()->with('item')->get();

        if ($lines->isEmpty()) {
            return back()->withErrors(['lines' => 'The request must contain at least one item.']);
        }
        if ($lines->contains(fn ($l) => empty($l->location_id))) {
            return back()->withErrors(['lines' => 'Each receiving item must have a location.']);
        }
        if ($lines->contains(fn ($l) => $l->item->item_category === 'EQUIPMENT' && (empty($l->asset_tag) || empty($l->serial_number)))) {
            return back()->withErrors(['lines' => 'Every equipment line requires an asset tag and serial number.']);
        }

        $receiving->update(['status' => 'PENDING', 'approved_by' => null, 'return_reason' => null]);

        $itemNames = $lines->map(fn ($l) => $l->item->item_name.' × '.$l->quantity)->implode('; ');
        TransactionLog::note($request->user(), "Resubmitted returned receiving request for supervisor approval. Items: {$itemNames}", $receiving->ref_no);

        return redirect()->route('receiving.index')->with('status', 'Receiving request resubmitted.');
    }

    protected function authorizeOwnReturned(Request $request, ReceivingTransaction $receiving): void
    {
        abort_unless($receiving->received_by === $request->user()->user_id, 403);
        abort_unless($receiving->status === 'RETURNED', 422, 'Only returned requests can be edited.');
    }

    protected function locationIdFor(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        return \App\Models\Location::whereRaw('LOWER(location_name) = ?', [strtolower($name)])->value('location_id');
    }
}
