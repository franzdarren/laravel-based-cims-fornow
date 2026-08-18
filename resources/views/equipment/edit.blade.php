@extends('layouts.app')
@section('title', 'Edit Equipment')
@section('heading', 'Edit equipment')
@section('subheading', $equipment->asset_tag.' — '.$equipment->item->item_name)

@section('content')
<div class="stack">
    <div class="card" style="max-width:600px">
        <div class="card-head"><h2>Equipment details</h2></div>
        <form method="POST" action="{{ route('equipment.update', $equipment) }}" class="stack">
            @csrf @method('PUT')
            <div class="field">
                <label>Brand</label>
                <input type="text" name="brand" value="{{ old('brand', $equipment->brand) }}">
            </div>
            <div class="field">
                <label>Model</label>
                <input type="text" name="model" value="{{ old('model', $equipment->model) }}">
            </div>
            <div class="field">
                <label>Location</label>
                <select name="location_id">
                    <option value="">— None —</option>
                    @foreach(\App\Models\Location::orderBy('location_name')->get() as $loc)
                        <option value="{{ $loc->location_id }}" @selected($equipment->location_id === $loc->location_id)>{{ $loc->location_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="req">Status</label>
                <select name="status" required>
                    @foreach(['AVAILABLE'=>'Available','UNDER_MAINTENANCE'=>'Under maintenance','MISSING'=>'Missing'] as $val=>$label)
                        <option value="{{ $val }}" @selected(old('status', $equipment->equipment_status) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="req">Reason for this edit</label>
                <input type="text" name="reason" placeholder="e.g. Sent for annual calibration" required>
            </div>
            <div class="actions">
                <button type="submit" class="btn primary">Save changes</button>
                <a href="{{ route('equipment.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>

    @if($equipment->equipment_status !== 'DISPOSED')
    <div class="card" id="dispose" style="max-width:600px">
        <div class="card-head"><h2>Dispose this unit</h2></div>
        <form method="POST" action="{{ route('equipment.dispose', $equipment) }}" class="stack" onsubmit="return confirm('Retire and dispose this equipment unit? This cannot be undone.');">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label class="req">Reason</label>
                    <select name="reason" required>
                        @foreach(['Beyond useful life','Damaged beyond repair','Unsafe for use','Other'] as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field span-2">
                    <label>Remarks</label>
                    <input type="text" name="remarks">
                </div>
            </div>
            <div class="actions"><button type="submit" class="btn danger">Record disposal</button></div>
        </form>
    </div>
    @endif
</div>
@endsection
