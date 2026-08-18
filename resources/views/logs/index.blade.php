@extends('layouts.app')
@section('title', 'Transaction Log')
@section('heading', 'Transaction log')
@section('subheading', 'Immutable audit trail of system activity')

@section('content')
<div class="card">
    <div class="card-head">
        <h2>Transaction log</h2>
        <form method="GET" action="{{ route('logs.index') }}" class="search-wrap">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search type, reference, user, or activity">
            <select name="type" onchange="this.form.submit()">
                <option value="">All transaction types</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" @selected($selectedType === $t)>{{ ucfirst(strtolower($t)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn small">Search</button>
        </form>
    </div>

    <div class="table-wrap">
        <table id="logTable">
            <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>User</th><th class="wrap">Activity</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="mono nowrap">{{ optional($log->date)->format('d M Y') }}</td>
                    <td><span class="badge blue">{{ $log->normalizedType() }}</span></td>
                    <td class="mono">{{ $log->reference_no }}</td>
                    <td>{{ $log->user->fullname ?? 'System' }}</td>
                    <td class="wrap">{{ $log->detail }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No matching activity found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $logs->links() }}
    </div>
</div>
@endsection
