@extends('layouts.app')
@section('title', 'Equipment')
@section('heading', 'Equipment')
@section('subheading', 'Track individual equipment units and open disposal dialogs')

@section('content')
<div class="stack">
    <div class="toolbar">
        <div class="search-wrap">
            <input type="text" data-table-search="equipmentTable" placeholder="Search equipment, asset tag, serial, or location">
        </div>
    </div>
    <div class="card">
        <div class="card-head"><h2>Equipment unit records</h2><span class="sub">One record per physical unit</span></div>
        <div class="table-wrap">
            <table id="equipmentTable" data-enhance>
                <thead><tr>
                    <th data-sort="text">Equipment</th>
                    <th data-sort="text">Asset tag</th>
                    <th>Serial no.</th>
                    <th>Brand / model</th>
                    <th data-sort="text">Location</th>
                    <th data-sort="text">Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($equipment as $e)
                    <tr>
                        <td><b>{{ $e->item->item_name }}</b><div class="muted small mono">{{ $e->item->item_code }}</div></td>
                        <td class="mono">{{ $e->asset_tag }}</td>
                        <td class="mono">{{ $e->serial_number ?: '—' }}</td>
                        <td>{{ trim(($e->brand ?? '').' '.($e->model ?? '')) ?: '—' }}</td>
                        <td>{{ $e->location->location_name ?? '—' }}</td>
                        <td>
                            @php $tone = match($e->equipment_status){'AVAILABLE'=>'green','ISSUED'=>'blue','UNDER_MAINTENANCE'=>'amber','MISSING'=>'red','DISPOSED'=>'red',default=>''}; @endphp
                            <span class="badge {{ $tone }}">{{ ucfirst(str_replace('_',' ',strtolower($e->equipment_status))) }}</span>
                        </td>
                        <td>
                            @if(auth()->user()->isRole('Nurse'))
                                @if($e->equipment_status !== 'DISPOSED')
                                    <div class="actions">
                                        <button type="button" class="btn small" data-modal-open="edit-equipment-{{ $e->equipment_id }}" data-modal-title="Edit {{ $e->asset_tag }}">Edit</button>
                                        <button type="button" class="btn small danger" data-modal-open="dispose-equipment-{{ $e->equipment_id }}" data-modal-title="Record equipment disposal">Dispose</button>
                                    </div>
                                @else
                                    <span class="muted small">Disposed record</span>
                                @endif
                            @else
                                <span class="muted small">View only</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="7" class="empty">No equipment records found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(auth()->user()->isRole('Nurse'))
@foreach($equipment as $e)
    @if($e->equipment_status !== 'DISPOSED')
        <template id="edit-equipment-{{ $e->equipment_id }}">
            <div class="context-summary"><b>{{ $e->item->item_name }}</b><span class="mono">{{ $e->asset_tag }}</span> · Serial: {{ $e->serial_number ?: '—' }} · {{ $e->brand }} {{ $e->model }} · {{ $e->location->location_name ?? '—' }}</div>
            <form method="POST" action="{{ route('equipment.update', $e) }}" class="stack">
                @csrf @method('PUT')
                <div class="form-grid">
                    <div class="field"><label>Brand</label><input type="text" name="brand" value="{{ $e->brand }}"></div>
                    <div class="field"><label>Model</label><input type="text" name="model" value="{{ $e->model }}"></div>
                    <div class="field span-2">
                        <label>Location</label>
                        <select name="location_id">
                            <option value="">— None —</option>
                            @foreach(\App\Models\Location::orderBy('location_name')->get() as $loc)
                                <option value="{{ $loc->location_id }}" @selected($e->location_id === $loc->location_id)>{{ $loc->location_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field span-2">
                        <label class="req">Status</label>
                        <select name="status" required>
                            @foreach(['AVAILABLE'=>'AVAILABLE','UNDER_MAINTENANCE'=>'UNDER_MAINTENANCE','MISSING'=>'MISSING'] as $val=>$label)
                                <option value="{{ $val }}" @selected($e->equipment_status === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field span-2"><label class="req">Adjustment reason</label><input type="text" name="reason" required></div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                    <button type="submit" class="btn primary">Save equipment status</button>
                </div>
            </form>
        </template>
        <template id="dispose-equipment-{{ $e->equipment_id }}">
            <div class="context-summary"><b>{{ $e->item->item_name }}</b><span class="mono">{{ $e->asset_tag }}</span> · {{ $e->brand }} {{ $e->model }}</div>
            <form method="POST" action="{{ route('equipment.dispose', $e) }}" class="stack">
                @csrf
                <div class="field">
                    <label class="req">Disposal reason</label>
                    <select name="reason" required>
                        @foreach(['Beyond useful life','Damaged beyond repair','Unsafe for use','Other'] as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="margin-top:11px"><label>Remarks</label><textarea name="remarks"></textarea></div>
                <div class="notice warn" style="margin-top:12px">Equipment is tracked per unit, so the disposal quantity is fixed at 1.</div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                    <button type="submit" class="btn danger">Record disposal</button>
                </div>
            </form>
        </template>
    @endif
@endforeach
@endif
@endsection
