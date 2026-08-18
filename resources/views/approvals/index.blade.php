@extends('layouts.app')
@section('title', 'Approvals')
@section('heading', 'Approvals')
@section('subheading', 'Approve or return nurse-submitted receiving requests')

@section('content')
<div class="card">
    <div class="card-head"><h2>Pending receiving approvals</h2><span class="badge amber">{{ $pending->count() }}</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Reference</th><th>Supplier</th><th>Date</th><th>Contents</th><th></th></tr></thead>
            <tbody>
            @forelse($pending as $r)
                <tr>
                    <td class="mono">{{ $r->ref_no }}</td>
                    <td>{{ $r->supplier->supplier_name }}</td>
                    <td class="mono nowrap">{{ optional($r->date_received)->format('d M Y') }}</td>
                    <td>
                        @foreach($r->lines as $l)
                            {{ $l->item->item_name }} × <span class="mono">{{ $l->quantity }}</span>@if(!$loop->last)<br>@endif
                        @endforeach
                    </td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn small" data-modal-open="review-receiving-{{ $r->receiving_transaction_id }}" data-modal-title="Review {{ $r->ref_no }}">Review</button>
                            <form method="POST" action="{{ route('approvals.approve', $r) }}" onsubmit="return confirm('Approve this delivery and post it to inventory?');">
                                @csrf
                                <button type="submit" class="btn small primary">Approve</button>
                            </form>
                            <button type="button" class="btn small danger" data-modal-open="return-receiving-{{ $r->receiving_transaction_id }}" data-modal-title="Return request to Nurse">Return</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No receiving requests are awaiting approval.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($pending as $r)
    <template id="review-receiving-{{ $r->receiving_transaction_id }}">
        <div class="context-summary">
            <b>Reference number: <span class="mono">{{ $r->ref_no }}</span></b>
            <div>{{ $r->supplier->supplier_name }} · {{ optional($r->date_received)->format('d M Y') }} · Encoded by {{ $r->receivedBy->fullname }}</div>
            <div style="margin-top:7px"><b style="display:inline">Remarks:</b> {{ $r->remarks ?: '—' }}</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th class="right">Quantity</th><th>Details</th></tr></thead>
                <tbody>
                @foreach($r->lines as $l)
                    @php $i = $l->item; $eq = $i->item_category === 'EQUIPMENT'; @endphp
                    <tr>
                        <td>{{ $i->item_name }}</td>
                        <td class="mono right">{{ $l->quantity }}</td>
                        <td class="mono small">
                            @if($eq)
                                Brand: {{ $l->brand ?: '—' }}<br>Model: {{ $l->model ?: '—' }}<br>Serial number: {{ $l->serial_number ?: '—' }}<br>Asset tag: {{ $l->asset_tag ?: '—' }}<br>Location: {{ $l->location->location_name ?? '—' }}
                            @else
                                Brand: {{ $l->brand ?: '—' }}<br>Batch number: {{ $l->batch_no ?: '—' }}<br>Expiry: {{ $l->expiry_date?->format('d M Y') ?: '—' }}<br>Location: {{ $l->location->location_name ?? '—' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="modal-actions"><button type="button" class="btn" onclick="CIMS.modal.close()">Close</button></div>
    </template>
    <template id="return-receiving-{{ $r->receiving_transaction_id }}">
        <div class="context-summary"><b>{{ $r->ref_no }}</b> The request will remain visible in its record history.</div>
        <form method="POST" action="{{ route('approvals.return', $r) }}" class="stack">
            @csrf
            <div class="field">
                <label class="req">Return reason</label>
                <textarea name="reason" maxlength="150" required></textarea>
                <div class="muted small">Maximum 150 characters</div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Back</button>
                <button type="submit" class="btn primary">Return request</button>
            </div>
        </form>
    </template>
@endforeach
@endsection
