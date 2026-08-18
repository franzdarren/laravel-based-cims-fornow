@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Clinic inventory overview')

@section('content')
<div class="stack">
    <div class="grid cols-4">
        <div class="card kpi shadow">
            <div class="label">Active item records</div>
            <div class="value">{{ $activeItemsCount }}</div>
            <div class="hint">Medicines, supplies, and equipment</div>
        </div>
        <div class="card kpi shadow">
            <div class="label">Near-expiry batches</div>
            <div class="value tone-amber">{{ $nearExpiryBatches->count() }}</div>
            <div class="hint">One {{ \App\Models\Setting::get('near_expiry_days', 90) }}-day threshold for all medicines</div>
        </div>
        <div class="card kpi shadow">
            <div class="label">Below reorder level</div>
            <div class="value tone-red">{{ $lowStockItems->count() }}</div>
            <div class="hint">Medicines and supplies needing attention</div>
        </div>
        <div class="card kpi shadow">
            <div class="label">{{ $fourthCard['label'] }}</div>
            <div class="value">{{ $fourthCard['value'] }}</div>
            <div class="hint">{{ $fourthCard['hint'] }}</div>
        </div>
    </div>

    <div class="split">
        <div class="card">
            <div class="card-head">
                <h2>Needs attention</h2>
                <span class="sub">Automatic inventory flags</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Signal</th><th>Item</th><th>Detail</th></tr></thead>
                    <tbody>
                        @forelse($nearExpiryBatches as $b)
                            <tr class="row-near">
                                <td><span class="badge amber">Near expiry</span></td>
                                <td><b>{{ $b->item->item_name }}</b></td>
                                <td class="mono small">{{ $b->batch_no }} expires {{ $b->expiry_date->format('d M Y') }}</td>
                            </tr>
                        @empty
                        @endforelse
                        @foreach($lowStockItems as $i)
                            <tr class="row-low">
                                <td><span class="badge red">Low stock</span></td>
                                <td><b>{{ $i->item_name }}</b></td>
                                <td class="mono small">{{ $i->stockOnHand() }} {{ $i->uom->uom_name ?? '' }} on hand; reorder level {{ $i->reorder_threshold }}
                                    @if($role === 'Nurse'); reorder quantity {{ $i->reorder_qty }}@endif
                                </td>
                            </tr>
                        @endforeach
                        @if($nearExpiryBatches->isEmpty() && $lowStockItems->isEmpty())
                            <tr><td colspan="3" class="muted">Nothing currently needs attention.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2>Role workflow</h2>
                <span class="sub">{{ $role }}</span>
            </div>
            <div class="timeline">
                @foreach($workflow as $step)
                    <div class="timeline-item">
                        <span class="dot"></span>
                        <div><b>{{ $step[0] }}</b><div class="muted small">{{ $step[1] }}</div></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if($canViewLogs)
    <div class="card">
        <div class="card-head">
            <h2>Recent transactions</h2>
            <a href="{{ route('logs.index') }}" class="btn small">Open full log</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>User</th><th>Activity</th></tr></thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="mono nowrap">{{ optional($log->date)->format('d M Y') }}</td>
                            <td><span class="badge blue">{{ $log->normalizedType() }}</span></td>
                            <td class="mono">{{ $log->reference_no }}</td>
                            <td>{{ $log->user->fullname ?? 'System' }}</td>
                            <td>{{ $log->detail }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">No activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
