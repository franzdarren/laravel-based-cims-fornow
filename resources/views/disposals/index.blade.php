@extends('layouts.app')
@section('title', 'Disposals')
@section('heading', 'Disposals')
@section('subheading', 'Review posted disposal records')

@section('content')
<div class="card">
    <div class="card-head">
        <h2>Disposed item records</h2>
        <div class="search-wrap"><input type="text" data-table-search="disposalTable" placeholder="Search disposal records"></div>
    </div>
    <div class="table-wrap">
        <table id="disposalTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Reference</th>
                <th data-sort="date">Date</th>
                <th data-sort="text">Item</th>
                <th>Batch / asset</th>
                <th data-sort="number" class="right">Quantity</th>
                <th>Reason</th>
                <th>Disposed by</th>
            </tr></thead>
            <tbody>
            @forelse($disposals as $log)
                @foreach($log->lines as $l)
                    @php
                        $item = $l->batch?->item ?? $l->equipment?->item;
                        $qty = $l->batch ? max(0, ($l->qty_before ?? 0) - ($l->qty_after ?? 0)) : 1;
                    @endphp
                    <tr>
                        <td class="mono">{{ $log->reference_no }}</td>
                        <td class="mono nowrap" data-sort-value="{{ $log->transaction_datetime }}">{{ $log->transaction_datetime->format('d M Y') }}</td>
                        <td>{{ $item->item_name ?? '—' }}</td>
                        <td class="mono">{{ $l->batch->batch_no ?? $l->equipment->asset_tag ?? '—' }}</td>
                        <td class="right mono">{{ $qty }}</td>
                        <td>{{ $log->reason }}</td>
                        <td>{{ $log->user->fullname ?? '—' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr class="empty-row"><td colspan="7" class="empty">No disposals recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
