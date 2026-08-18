@extends('layouts.app')
@section('title', 'Review '.$receiving->ref_no)
@section('heading', 'Review '.$receiving->ref_no)
@section('subheading', 'Approvals')

@section('content')
<div class="stack">
    <div class="card">
        <div class="context-summary">
            <b>Reference number: <span class="mono">{{ $receiving->ref_no }}</span></b><br>
            {{ $receiving->supplier->supplier_name }} · {{ optional($receiving->date_received)->format('d M Y') }} · Encoded by {{ $receiving->receivedBy->fullname }}
            @if($receiving->remarks)
                <br><span class="muted small">Remarks: {{ $receiving->remarks }}</span>
            @endif
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Details</th><th>Qty</th></tr></thead>
                <tbody>
                @foreach($receiving->lines as $l)
                    <tr>
                        <td>{{ $l->item->item_name }}</td>
                        <td class="mono small">
                            @if($l->item->item_category === 'EQUIPMENT')
                                {{ $l->model }} @ {{ $l->location->location_name ?? '—' }}
                            @else
                                {{ $l->batch_no }} @if($l->expiry_date) exp {{ $l->expiry_date->format('d M Y') }} @endif
                            @endif
                            @if($l->brand) · {{ $l->brand }} @endif
                        </td>
                        <td class="mono right">{{ $l->quantity }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <hr>

        <div class="actions">
            <form method="POST" action="{{ route('approvals.approve', $receiving) }}" onsubmit="return confirm('Approve this delivery and post it to inventory?');">
                @csrf
                <button type="submit" class="btn primary">Approve</button>
            </form>
            <form method="POST" action="{{ route('approvals.return', $receiving) }}" onsubmit="return attachReason(this)">
                @csrf
                <input type="hidden" name="reason" class="return-reason-field">
                <button type="submit" class="btn danger">Return</button>
            </form>
            <a href="{{ route('approvals.index') }}" class="btn">Back</a>
        </div>
    </div>
</div>

<script>
function attachReason(form){
    var reason = prompt('Reason for returning this request to the Nurse:');
    if(!reason){ return false; }
    form.querySelector('.return-reason-field').value = reason;
    return true;
}
</script>
@endsection
