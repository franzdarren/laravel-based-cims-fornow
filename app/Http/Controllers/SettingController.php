<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\Location;
use App\Models\ReceivingTransactionLine;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $nearExpiryDays = (int) Setting::get('near_expiry_days', 90);
        $reorderItems = InventoryItem::active()->where('item_category', '!=', 'EQUIPMENT')->orderBy('item_name')->get();

        $uoms = UnitOfMeasurement::withCount('inventoryItems')->orderBy('uom_name')->get();
        $locations = Location::orderBy('location_name')->get()->map(fn (Location $loc) => [
            'location' => $loc,
            'line_usage' => ReceivingTransactionLine::where('location_id', $loc->location_id)->count(),
            'equipment_usage' => Equipment::where('location_id', $loc->location_id)->count(),
        ]);

        return view('settings.edit', compact('nearExpiryDays', 'reorderItems', 'uoms', 'locations'));
    }

    public function updateGlobal(Request $request)
    {
        $data = $request->validate([
            'near_expiry_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        Setting::set('near_expiry_days', (string) $data['near_expiry_days']);

        TransactionLog::note(auth()->user(), "Updated global near-expiry setting to {$data['near_expiry_days']} days");

        return redirect()->route('settings.edit')->with('status', 'Global setting saved.');
    }

    public function updateReorder(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'reorder_threshold' => ['required', 'integer', 'min:0'],
            'reorder_qty' => ['required', 'integer', 'min:0'],
        ]);

        $item->update($data);

        TransactionLog::note(auth()->user(), "Updated reorder settings for {$item->item_name}", $item->item_code);

        return redirect()->route('settings.edit')->with('status', 'Reorder settings updated.');
    }

    public function addUom(Request $request)
    {
        $data = $request->validate(['value' => ['required', 'string', 'max:50']]);
        $value = trim(preg_replace('/\s+/', ' ', $data['value']));

        if (UnitOfMeasurement::whereRaw('LOWER(uom_name) = ?', [strtolower($value)])->exists()) {
            return back()->withErrors(['value' => 'That Unit of Measurement already exists.']);
        }

        UnitOfMeasurement::create(['uom_name' => $value]);

        TransactionLog::note(auth()->user(), "Added Unit of Measurement: {$value}");

        return redirect()->route('settings.edit')->with('status', 'Unit of Measurement added.');
    }

    public function addLocation(Request $request)
    {
        $data = $request->validate(['value' => ['required', 'string', 'max:100']]);
        $value = trim(preg_replace('/\s+/', ' ', $data['value']));

        if (Location::whereRaw('LOWER(location_name) = ?', [strtolower($value)])->exists()) {
            return back()->withErrors(['value' => 'That location already exists.']);
        }

        Location::create(['location_name' => $value]);

        TransactionLog::note(auth()->user(), "Added location: {$value}");

        return redirect()->route('settings.edit')->with('status', 'Location added.');
    }

    public static function locationList(): array
    {
        return Location::orderBy('location_name')->pluck('location_name')->all();
    }

    public static function uomList(): array
    {
        return UnitOfMeasurement::orderBy('uom_name')->pluck('uom_name')->all();
    }
}
