<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Models\TransactionLog;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\Request;

class ItemMasterController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with(['supplier', 'uom'])->orderBy('item_code')->get();
        $suppliers = Supplier::active()->orderBy('supplier_name')->get();
        $uoms = UnitOfMeasurement::orderBy('uom_name')->get();

        return view('items.index', compact('items', 'suppliers', 'uoms'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('supplier_name')->get();
        $uoms = UnitOfMeasurement::orderBy('uom_name')->get();

        return view('items.create', compact('suppliers', 'uoms'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['item_status'] = 'active';

        $item = InventoryItem::create($data);

        TransactionLog::note(auth()->user(), "Created item master record for {$item->item_name}", $item->item_code);

        return redirect()->route('items.index')->with('status', 'Item added.');
    }

    public function edit(InventoryItem $item)
    {
        $suppliers = Supplier::active()->orderBy('supplier_name')->get();
        $uoms = UnitOfMeasurement::orderBy('uom_name')->get();

        return view('items.edit', compact('item', 'suppliers', 'uoms'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $data = $this->validated($request, $item);

        $item->update($data);

        TransactionLog::note(auth()->user(), "Edited item master record for {$item->item_name}", $item->item_code);

        return redirect()->route('items.index')->with('status', 'Item updated.');
    }

    public function destroy(InventoryItem $item)
    {
        if ($item->item_status !== 'active') {
            return redirect()->route('items.index')->withErrors(['item' => 'Item is already inactive.']);
        }

        if ($item->hasActiveRecords()) {
            return redirect()->route('items.index')->withErrors(['item' => 'Item cannot be deleted while an active batch or equipment record exists.']);
        }

        // Soft delete: the record becomes inactive rather than being removed,
        // so historical batches/equipment/transactions referring to it stay valid.
        $item->update(['item_status' => 'inactive']);

        TransactionLog::note(auth()->user(), "Deleted (deactivated) item master record for {$item->item_name}", $item->item_code);

        return redirect()->route('items.index')->with('status', 'Item deleted.');
    }

    public function reactivate(InventoryItem $item)
    {
        $item->update(['item_status' => 'active']);

        TransactionLog::note(auth()->user(), "Reactivated item master record for {$item->item_name}", $item->item_code);

        return redirect()->route('items.index')->with('status', 'Item reactivated.');
    }

    protected function validated(Request $request, ?InventoryItem $item = null): array
    {
        $uniqueRule = 'unique:inventory_items,item_code'.($item ? ','.$item->item_id.',item_id' : '');

        $data = $request->validate([
            'item_code' => ['required', 'string', 'max:50', $uniqueRule],
            'item_name' => ['required', 'string', 'max:255'],
            'item_category' => ['required', 'in:MEDICINE,SUPPLY,EQUIPMENT'],
            'uom_id' => ['required', 'exists:unit_of_measurement,uom_id'],
            'supplier_id' => ['nullable', 'exists:supplier,supplier_id'],
            'reorder_threshold' => ['required', 'integer', 'min:0'],
            'reorder_qty' => ['required', 'integer', 'min:0'],
        ]);

        return $data;
    }
}
