@extends('layouts.app')
@section('title', 'Batches')
@section('heading', 'Batches')
@section('subheading', 'View stock batches and record disposals')

@section('content')
<div class="stack">
    <div class="toolbar">
        <div class="search-wrap">
            <input type="text" data-table-search="batchTable" placeholder="Search item, batch, brand, or status">
            <select id="batchStatusFilter">
                <option value="">All statuses</option>
                <option value="ACTIVE">Active</option>
                <option value="INACTIVE">Inactive</option>
                <option value="FOR_DISPOSAL">For disposal</option>
                <option value="DISPOSED">Disposed</option>
            </select>
        </div>
    </div>
    <div class="card">
        <div class="card-head"><h2>Batch records</h2></div>
        <div class="table-wrap">
            <table id="batchTable" data-enhance>
                <thead><tr>
                    <th data-sort="text">Item</th>
                    <th data-sort="text">Batch</th>
                    <th>Brand</th>
                    <th data-sort="date">Expiry</th>
                    <th data-sort="number" class="right">On hand</th>
                    <th>Unit</th>
                    <th data-sort="text">Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($batches as $b)
                    <tr data-status="{{ $b->batch_status }}" class="{{ $b->isNearExpiry() ? 'row-near' : '' }}">
                        <td><b>{{ $b->item->item_name }}</b><div class="muted small">{{ $b->item->item_code }}</div></td>
                        <td class="mono">{{ $b->batch_no }}</td>
                        <td>{{ $b->brand ?: '—' }}</td>
                        <td class="mono nowrap" data-sort-value="{{ $b->expiry_date }}">{{ $b->expiry_date?->format('d M Y') ?? '—' }}</td>
                        <td class="right mono">{{ $b->quantity_on_hand }}</td>
                        <td>{{ $b->item->uom->uom_name ?? '—' }}</td>
                        <td>
                            @php $tone = match($b->batch_status){'ACTIVE'=>'green','DISPOSED'=>'red','FOR_DISPOSAL'=>'amber',default=>''}; @endphp
                            <span class="badge {{ $tone }}">{{ ucfirst(strtolower($b->batch_status)) }}</span>
                            @if($b->isNearExpiry())<span class="badge amber">Near expiry</span>@endif
                        </td>
                        <td>
                            @if(auth()->user()->isRole('Nurse'))
                                <div class="actions">
                                    <button type="button" class="btn small" data-modal-open="edit-batch-{{ $b->batch_id }}" data-modal-title="Edit batch record">Edit</button>
                                    @if($b->batch_status === 'ACTIVE' && $b->quantity_on_hand > 0)
                                        <button type="button" class="btn small danger" data-modal-open="dispose-batch-{{ $b->batch_id }}" data-modal-title="Record batch disposal">Dispose</button>
                                    @endif
                                </div>
                            @else
                                <span class="muted small">View only</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="8" class="empty">No batches found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(auth()->user()->isRole('Nurse'))
@foreach($batches as $b)
    <template id="edit-batch-{{ $b->batch_id }}">
        <div class="context-summary"><b>{{ $b->item->item_name }}</b> <span class="mono">{{ $b->batch_no }}</span> · Current on hand: {{ $b->quantity_on_hand }} {{ $b->item->uom->uom_name ?? '' }}</div>
        <form method="POST" action="{{ route('batches.update', $b) }}" class="stack">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="field"><label class="req">Batch number</label><input type="text" class="mono" name="batch_number" value="{{ $b->batch_no }}" required></div>
                <div class="field"><label>Brand</label><input type="text" name="brand" value="{{ $b->brand }}"></div>
                <div class="field"><label>Expiry date</label><input type="date" name="expiry_date" value="{{ optional($b->expiry_date)->format('Y-m-d') }}"></div>
                <div class="field"><label class="req">On-hand quantity</label><input type="number" min="0" name="qty_on_hand" value="{{ $b->quantity_on_hand }}" required></div>
                <div class="field span-4"><label class="req">Reason for edit</label><input type="text" name="reason" placeholder="e.g., Corrected physical count or encoded batch details" required></div>
            </div>
            <div class="notice" style="margin-top:12px">Changes are saved immediately and recorded in the transaction log. Supervisor approval is not required.</div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save changes</button>
            </div>
        </form>
    </template>
    @if($b->batch_status === 'ACTIVE' && $b->quantity_on_hand > 0)
        <template id="dispose-batch-{{ $b->batch_id }}">
            <div class="context-summary"><b>{{ $b->item->item_name }}</b> <span class="mono">{{ $b->batch_no }}</span> · {{ $b->quantity_on_hand }} {{ $b->item->uom->uom_name ?? '' }} on hand</div>
            <form method="POST" action="{{ route('batches.dispose', $b) }}" class="stack">
                @csrf
                <div class="form-grid">
                    <div class="field"><label class="req">Quantity to dispose</label><input type="number" min="1" max="{{ $b->quantity_on_hand }}" name="quantity" value="1" required></div>
                    <div class="field span-3">
                        <label class="req">Disposal reason</label>
                        <select name="reason" required>
                            @foreach(['Expired','Damaged','Contaminated','Packaging issue','Other'] as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field span-4"><label>Remarks</label><textarea name="remarks"></textarea></div>
                </div>
                <div class="notice warn" style="margin-top:12px">Submitting reduces this batch's on-hand quantity and writes a disposal record.</div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                    <button type="submit" class="btn danger">Record disposal</button>
                </div>
            </form>
        </template>
    @endif
@endforeach
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('batchTable');
    const status = document.getElementById('batchStatusFilter');
    status.addEventListener('change', () => {
        [...table.tBodies[0].rows].forEach(row => {
            if (row.classList.contains('empty-row')) return;
            row.dataset.filterHidden = (!status.value || row.dataset.status === status.value) ? '0' : '1';
        });
        CIMS.tables.refresh(table);
    });
});
</script>
@endsection
