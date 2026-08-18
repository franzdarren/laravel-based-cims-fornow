@extends('layouts.app')
@section('title', 'System Settings')
@section('heading', 'System Settings')
@section('subheading', 'The rules that quietly run the whole operation')

@section('content')
<div class="stack">
    <div class="card" style="max-width:520px">
        <div class="card-head"><h2>Global near-expiry threshold</h2></div>
        <p class="muted small">Every medicine batch uses this one value. Changing it updates the Dashboard and Batches flags for all medicines immediately — it cannot be set per item or per batch.</p>
        <form method="POST" action="{{ route('settings.global') }}" class="stack">
            @csrf @method('PUT')
            <div class="field">
                <label class="req">Near-expiry days</label>
                <input type="number" min="1" max="365" name="near_expiry_days" value="{{ old('near_expiry_days', $nearExpiryDays) }}" required>
            </div>
            <div class="actions"><button type="submit" class="btn primary">Save global setting</button></div>
        </form>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Units of Measurement</h2>
            <span class="sub">Available in the Item Master Unit of Measure dropdown</span>
            <button type="button" class="btn primary" data-modal-open="new-uom-template" data-modal-title="Add Unit of Measurement">Add Unit of Measurement</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Unit of Measurement</th><th>Used by item records</th></tr></thead>
                <tbody>
                @forelse($uoms as $u)
                    <tr><td><b>{{ $u->uom_name }}</b></td><td class="mono">{{ $u->inventory_items_count }}</td></tr>
                @empty
                    <tr><td colspan="2" class="empty">No Units of Measurement configured.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Locations</h2>
            <span class="sub">Available in Receiving location dropdowns</span>
            <button type="button" class="btn primary" data-modal-open="new-location-template" data-modal-title="Add Location">Add Location</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Location</th><th>Used by receiving lines</th><th>Equipment units</th></tr></thead>
                <tbody>
                @forelse($locations as $row)
                    <tr><td><b>{{ $row['location']->location_name }}</b></td><td class="mono">{{ $row['line_usage'] }}</td><td class="mono">{{ $row['equipment_usage'] }}</td></tr>
                @empty
                    <tr><td colspan="3" class="empty">No locations configured.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Reorder levels by item</h2>
            <span class="sub">Per-item — set individually below</span>
        </div>

        {{-- One empty form per row, kept outside the table so the HTML stays
             valid; each row's inputs/button link back via the form="" attribute. --}}
        @foreach($reorderItems as $i)
            <form id="reorder-form-{{ $i->item_id }}" method="POST" action="{{ route('settings.reorder', $i) }}">
                @csrf @method('PUT')
            </form>
        @endforeach

        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Reorder level</th><th>Reorder quantity</th><th></th></tr></thead>
                <tbody>
                @foreach($reorderItems as $i)
                    <tr>
                        <td>{{ $i->item_name }} <span class="mono small muted">({{ $i->item_code }})</span></td>
                        <td><input form="reorder-form-{{ $i->item_id }}" type="number" min="0" name="reorder_threshold" value="{{ $i->reorder_threshold }}" style="width:90px"></td>
                        <td><input form="reorder-form-{{ $i->item_id }}" type="number" min="0" name="reorder_qty" value="{{ $i->reorder_qty }}" style="width:90px"></td>
                        <td><button form="reorder-form-{{ $i->item_id }}" type="submit" class="btn small">Save</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<template id="new-uom-template">
    <form method="POST" action="{{ route('settings.uoms.add') }}" class="stack">
        @csrf
        <div class="field">
            <label class="req">Unit of Measurement</label>
            <input type="text" name="value" placeholder="e.g., ampule, mL, sachet" required>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
            <button type="submit" class="btn primary">Add Unit of Measurement</button>
        </div>
    </form>
</template>

<template id="new-location-template">
    <form method="POST" action="{{ route('settings.locations.add') }}" class="stack">
        @csrf
        <div class="field">
            <label class="req">Location name</label>
            <input type="text" name="value" placeholder="e.g., Treatment Room 2" required>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
            <button type="submit" class="btn primary">Add Location</button>
        </div>
    </form>
</template>
@endsection
