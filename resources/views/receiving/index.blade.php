@extends('layouts.app')
@section('title', 'Receiving Records')
@section('heading', 'Receiving Records')
@section('subheading', 'Review all receiving transactions')
@section('top-actions')
    <a href="{{ route('receiving.create') }}" class="btn primary">New receiving transaction</a>
@endsection

@section('content')
<div class="stack">
    <div class="card">
        <div class="card-head"><h2>Pending requests</h2><span class="badge amber">{{ $myPending->count() }} pending</span></div>
        <div class="table-wrap">
            <table id="pendingReceivingTable" data-enhance>
                <thead><tr><th data-sort="text">Reference</th><th data-sort="date">Date</th><th>Supplier</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($myPending as $r)
                    <tr>
                        <td class="mono">{{ $r->ref_no }}</td>
                        <td class="mono nowrap" data-sort-value="{{ $r->date_received }}">{{ optional($r->date_received)->format('d M Y') }}</td>
                        <td>{{ $r->supplier->supplier_name }}</td>
                        <td><span class="badge amber">Pending</span></td>
                        <td><button type="button" class="btn small danger" data-modal-open="cancel-receiving-{{ $r->receiving_transaction_id }}" data-modal-title="Cancel pending request">Cancel request</button></td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="5" class="empty">You have no pending requests.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Returned requests</h2><span class="badge amber">{{ $myReturned->count() }} returned</span></div>
        <div class="card-body stack">
        @forelse($myReturned as $r)
            <div class="card">
                <div class="card-head">
                    <h3 class="mono">{{ $r->ref_no }}</h3>
                    <div class="actions">
                        <button type="button" class="btn small" data-modal-open="edit-returned-details-{{ $r->receiving_transaction_id }}" data-modal-title="Edit receiving details">Edit transaction details</button>
                        <form method="POST" action="{{ route('receiving.returned.resubmit', $r) }}">
                            @csrf
                            <button type="submit" class="btn small primary">Resubmit for approval</button>
                        </form>
                    </div>
                </div>
                <div class="grid cols-4" style="margin-bottom:10px">
                    <div><b>Supplier</b><div>{{ $r->supplier->supplier_name }}</div></div>
                    <div><b>Date received</b><div class="mono">{{ optional($r->date_received)->format('d M Y') }}</div></div>
                    <div><b>Remarks</b><div>{{ $r->remarks ?: '—' }}</div></div>
                    <div><b>Return reason</b><div>{{ $r->return_reason ?: '—' }}</div></div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Item</th><th>Quantity</th><th>Details</th><th></th></tr></thead>
                        <tbody>
                        @foreach($r->lines as $l)
                            @php $i = $l->item; $eq = $i->item_category === 'EQUIPMENT'; @endphp
                            <tr>
                                <td><b>{{ $i->item_name }}</b><div class="muted small">{{ $i->item_code }}</div></td>
                                <td class="mono">{{ $l->quantity }}</td>
                                <td class="mono small">
                                    @if($eq)
                                        Brand: {{ $l->brand ?: '—' }} · Model: {{ $l->model ?: '—' }} · Serial: {{ $l->serial_number ?: '—' }} · Asset tag: {{ $l->asset_tag ?: '—' }} · Location: {{ $l->location->location_name ?? '—' }}
                                    @else
                                        Brand: {{ $l->brand ?: '—' }} · Batch: {{ $l->batch_no ?: '—' }} · Expiry: {{ $l->expiry_date?->format('d M Y') ?: '—' }} · Location: {{ $l->location->location_name ?? '—' }}
                                    @endif
                                </td>
                                <td><button type="button" class="btn small" data-modal-open="edit-returned-line-{{ $l->receiving_transaction_line_id }}" data-modal-title="Edit returned item">Edit item</button></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="empty">You have no returned requests.</div>
        @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Recent receiving records</h2></div>
        <div class="toolbar"><div class="search-wrap"><input type="text" data-table-search="recentReceivingTable" placeholder="Search recent receiving records"></div></div>
        <div class="table-wrap">
            <table id="recentReceivingTable" data-enhance>
                <thead><tr><th data-sort="text">Reference</th><th>Supplier</th><th data-sort="date">Date</th><th data-sort="number" class="right">Items</th><th data-sort="text">Status</th></tr></thead>
                <tbody>
                @forelse($recent as $r)
                    @php $tone = match($r->status){'APPROVED'=>'green','CANCELLED'=>'red',default=>''}; @endphp
                    <tr>
                        <td class="mono">{{ $r->ref_no }}</td>
                        <td>{{ $r->supplier->supplier_name }}</td>
                        <td class="mono nowrap" data-sort-value="{{ $r->date_received }}">{{ optional($r->date_received)->format('d M Y') }}</td>
                        <td class="right mono">{{ $r->lines()->count() }}</td>
                        <td><span class="badge {{ $tone }}">{{ ucfirst(strtolower($r->status)) }}</span></td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="5" class="empty">No completed receiving records yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($myPending as $r)
    <template id="cancel-receiving-{{ $r->receiving_transaction_id }}">
        <div class="notice warn"><b>Receiving request {{ $r->ref_no }}</b><br>This removes the request from the Supervisor approval queue. The cancellation remains in the audit log.</div>
        <form method="POST" action="{{ route('receiving.cancel', $r) }}" class="stack" style="margin-top:12px">
            @csrf
            <div class="field">
                <label class="req">Cancellation reason</label>
                <textarea name="reason" placeholder="Enter the reason for cancelling this request" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Keep request</button>
                <button type="submit" class="btn danger">Cancel pending request</button>
            </div>
        </form>
    </template>
@endforeach

@foreach($myReturned as $r)
    <template id="edit-returned-details-{{ $r->receiving_transaction_id }}">
        <form method="POST" action="{{ route('receiving.returned.details', $r) }}" class="stack">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="field span-2">
                    <label class="req">Supplier</label>
                    <select name="supplier_id" required>
                        @foreach(\App\Models\Supplier::where('status', 'active')->orWhere('supplier_id', $r->supplier_id)->orderBy('supplier_name')->get() as $s)
                            <option value="{{ $s->supplier_id }}" @selected($s->supplier_id === $r->supplier_id)>{{ $s->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="req">Reference number</label>
                    <input type="text" class="mono" name="reference_no" value="{{ $r->ref_no }}" required>
                </div>
                <div class="field">
                    <label class="req">Date received</label>
                    <input type="date" name="date_received" value="{{ optional($r->date_received)->format('Y-m-d') }}" required>
                </div>
                <div class="field span-4">
                    <label>Remarks</label>
                    <textarea name="remarks" maxlength="150">{{ $r->remarks }}</textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save details</button>
            </div>
        </form>
    </template>
    @foreach($r->lines as $l)
        @php $i = $l->item; $eq = $i->item_category === 'EQUIPMENT'; @endphp
        <template id="edit-returned-line-{{ $l->receiving_transaction_line_id }}">
            <form method="POST" action="{{ route('receiving.returned.line', [$r, $l]) }}" class="stack">
                @csrf @method('PUT')
                <div class="context-summary"><b>{{ $i->item_name }}</b> <span class="mono">{{ $i->item_code }}</span></div>
                <div class="form-grid">
                    <div class="field">
                        <label class="req">Quantity</label>
                        <input type="number" min="1" @if($eq) max="1" @endif name="quantity" value="{{ $l->quantity }}" required>
                    </div>
                    <div class="field">
                        <label>Brand</label>
                        <input type="text" name="brand" value="{{ $l->brand }}">
                    </div>
                    @if($eq)
                        <div class="field">
                            <label>Equipment model</label>
                            <input type="text" name="model" value="{{ $l->model }}">
                        </div>
                        <div class="field">
                            <label class="req">Serial number</label>
                            <input type="text" class="mono" name="serial_number" value="{{ $l->serial_number }}" required>
                        </div>
                        <div class="field">
                            <label class="req">Asset tag</label>
                            <input type="text" class="mono" name="asset_tag" value="{{ $l->asset_tag }}" required>
                        </div>
                    @else
                        <div class="field">
                            <label>Batch number <span class="muted">(optional)</span></label>
                            <input type="text" class="mono" name="batch_number" value="{{ $l->batch_no }}">
                        </div>
                        <div class="field">
                            <label>Expiry date</label>
                            <input type="date" name="expiry_date" value="{{ $l->expiry_date?->format('Y-m-d') }}">
                        </div>
                    @endif
                    <div class="field">
                        <label class="req">Location</label>
                        <select name="location" required>
                            <option value="">Select location</option>
                            @foreach(\App\Http\Controllers\SettingController::locationList() as $loc)
                                <option value="{{ $loc }}" @selected(($l->location->location_name ?? null) === $loc)>{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                    <button type="submit" class="btn primary">Save item</button>
                </div>
            </form>
        </template>
    @endforeach
@endforeach
@endsection
