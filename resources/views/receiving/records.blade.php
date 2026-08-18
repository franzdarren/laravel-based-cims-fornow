@extends('layouts.app')
@section('title', 'Receiving Records')
@section('heading', 'Receiving Records')
@section('subheading', 'Review all receiving transactions')

@section('content')
<div class="card">
    <div class="card-head">
        <h2>Receiving transaction records</h2>
        <div class="search-wrap"><input type="text" data-table-search="receivingRecordsTable" placeholder="Search reference, supplier, or status"></div>
    </div>
    <div class="table-wrap">
        <table id="receivingRecordsTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Reference</th>
                <th data-sort="date">Date</th>
                <th data-sort="text">Supplier</th>
                <th>Encoded by</th>
                <th>Approved by</th>
                <th data-sort="text">Status</th>
            </tr></thead>
            <tbody>
            @forelse($records as $r)
                @php $tone = match($r->status){'APPROVED'=>'green','PENDING'=>'amber','RETURNED'=>'red','CANCELLED'=>'red',default=>''}; @endphp
                <tr>
                    <td class="mono">{{ $r->ref_no }}</td>
                    <td class="mono nowrap" data-sort-value="{{ $r->date_received }}">{{ optional($r->date_received)->format('d M Y') }}</td>
                    <td>{{ $r->supplier->supplier_name }}</td>
                    <td>{{ $r->receivedBy->fullname ?? '—' }}</td>
                    <td>{{ $r->approvedBy->fullname ?? '—' }}</td>
                    <td><span class="badge {{ $tone }}">{{ ucfirst(strtolower($r->status)) }}</span></td>
                </tr>
            @empty
                <tr class="empty-row"><td colspan="6" class="empty">No receiving records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
