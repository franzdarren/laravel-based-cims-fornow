@extends('layouts.app')
@section('title', 'Edit Batch')
@section('heading', 'Edit batch')
@section('subheading', $batch->item->item_name.' — '.$batch->batch_no)

@section('content')
<div class="stack">
    <div class="card" style="max-width:600px">
        <div class="card-head"><h2>Batch details</h2></div>
        <form method="POST" action="{{ route('batches.update', $batch) }}" class="stack">
            @csrf @method('PUT')
            <div class="field">
                <label class="req">Batch number</label>
                <input type="text" name="batch_number" value="{{ old('batch_number', $batch->batch_no) }}" required>
            </div>
            <div class="field">
                <label>Brand</label>
                <input type="text" name="brand" value="{{ old('brand', $batch->brand) }}">
            </div>
            <div class="field">
                <label>Expiry date</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($batch->expiry_date)->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label class="req">Quantity on hand</label>
                <input type="number" min="0" name="qty_on_hand" value="{{ old('qty_on_hand', $batch->quantity_on_hand) }}" required>
            </div>
            <div class="field">
                <label class="req">Reason for this edit</label>
                <input type="text" name="reason" placeholder="e.g. Corrected a typo in the batch number" required>
            </div>
            <div class="actions">
                <button type="submit" class="btn primary">Save changes</button>
                <a href="{{ route('batches.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>

    @if($batch->batch_status === 'ACTIVE' && $batch->quantity_on_hand > 0)
    <div class="card" id="dispose" style="max-width:600px">
        <div class="card-head"><h2>Dispose from this batch</h2><span class="sub">Currently {{ $batch->quantity_on_hand }} {{ $batch->item->uom->uom_name ?? '' }} on hand</span></div>
        <form method="POST" action="{{ route('batches.dispose', $batch) }}" class="stack" onsubmit="return confirm('Record this disposal? This cannot be undone.');">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label class="req">Quantity to dispose</label>
                    <input type="number" min="1" max="{{ $batch->quantity_on_hand }}" name="quantity" required>
                </div>
                <div class="field">
                    <label class="req">Reason</label>
                    <select name="reason" required>
                        @foreach(['Expired','Damaged','Contaminated','Packaging issue','Other'] as $r)
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
