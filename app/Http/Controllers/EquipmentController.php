<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Location;
use App\Models\TransactionLog;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::with(['item', 'location'])->orderBy('asset_tag')->get();

        return view('equipment.index', compact('equipment'));
    }

    public function edit(Equipment $equipmentItem)
    {
        return view('equipment.edit', ['equipment' => $equipmentItem]);
    }

    public function update(Request $request, Equipment $equipmentItem)
    {
        $data = $request->validate([
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'location_id' => ['nullable', 'exists:location,location_id'],
            'status' => ['required', 'in:AVAILABLE,UNDER_MAINTENANCE,MISSING'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $before = $equipmentItem->equipment_status;

        $equipmentItem->update([
            'brand' => $data['brand'],
            'model' => $data['model'],
            'location_id' => $data['location_id'] ?? null,
            'equipment_status' => $data['status'],
        ]);

        $log = TransactionLog::create([
            'transaction_type' => 'ADJUSTMENT',
            'user_id' => auth()->id(),
            'reference_no' => $equipmentItem->asset_tag,
            'reason' => $data['reason'],
        ]);
        $log->lines()->create([
            'equipment_id' => $equipmentItem->equipment_id,
            'status_before' => $before,
            'status_after' => $data['status'],
            'line_remarks' => "Updated {$equipmentItem->item->item_name} {$equipmentItem->asset_tag}: status {$before} → {$data['status']}. Reason: {$data['reason']}",
        ]);

        return redirect()->route('equipment.index')->with('status', 'Equipment record updated.');
    }

    public function dispose(Request $request, Equipment $equipmentItem)
    {
        abort_if($equipmentItem->equipment_status === 'DISPOSED', 422, 'This equipment has already been disposed.');

        $data = $request->validate([
            'reason' => ['required', 'in:Beyond useful life,Damaged beyond repair,Unsafe for use,Other'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $equipmentItem->equipment_status;
        $equipmentItem->update(['equipment_status' => 'DISPOSED']);

        $log = TransactionLog::create([
            'transaction_type' => 'DISPOSAL',
            'user_id' => $request->user()->user_id,
            'reference_no' => TransactionLog::nextDisposalRef(),
            'reason' => $data['reason'].($data['remarks'] ? " — {$data['remarks']}" : ''),
        ]);
        $log->lines()->create([
            'equipment_id' => $equipmentItem->equipment_id,
            'status_before' => $before,
            'status_after' => 'DISPOSED',
            'line_remarks' => "Disposed equipment {$equipmentItem->asset_tag} ({$equipmentItem->item->item_name})",
        ]);

        return redirect()->route('equipment.index')->with('status', "Disposal {$log->reference_no} recorded.");
    }
}
