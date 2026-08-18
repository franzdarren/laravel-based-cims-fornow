@extends('layouts.app')
@section('title', 'Issuance Records')
@section('heading', 'Issuance records')
@section('subheading', 'Review and edit recorded medicine and supply issuances')
@section('top-actions')
    <a href="{{ route('issuance.create') }}" class="btn primary">New issuance</a>
@endsection

@section('content')
<div class="card">
    <div class="card-head">
        <h2>Issuance records</h2>
        <div class="search-wrap"><input type="text" data-table-search="issuanceTable" placeholder="Search issuance records"></div>
    </div>
    <div class="table-wrap">
        <table id="issuanceTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Reference</th>
                <th data-sort="date">Date</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Visit details</th>
                <th>Issued lines</th>
                <th data-sort="text">Status</th>
                <th></th>
            </tr></thead>
            <tbody>
            @forelse($issuances as $iss)
                <tr>
                    <td class="mono">{{ $iss->reference_no }}</td>
                    <td class="mono nowrap" data-sort-value="{{ $iss->date }}">{{ optional($iss->date)->format('d M Y') }}</td>
                    <td><b>{{ $iss->employee_name ?: '—' }}</b><div class="muted small mono">{{ $iss->employee_no }}</div></td>
                    <td>{{ $iss->department ?: '—' }}</td>
                    <td>{{ $iss->chief_complaint }}<div class="muted small">{{ $iss->disposition }}</div></td>
                    <td class="small">
                        @foreach($iss->lines as $l)
                            {{ $l->batch->item->item_name ?? '—' }} · <span class="mono">{{ $l->batch->batch_no ?? '—' }} × {{ $l->quantity_issued }}</span>@if(!$loop->last)<br>@endif
                        @endforeach
                    </td>
                    <td><span class="badge green">{{ $iss->status }}</span></td>
                    <td><button type="button" class="btn small" data-modal-open="edit-issuance-{{ $iss->issuance_transaction_id }}" data-modal-title="Edit issuance record">Edit</button></td>
                </tr>
            @empty
                <tr class="empty-row"><td colspan="8" class="empty">No issuance records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($issuances as $iss)
    <template id="edit-issuance-{{ $iss->issuance_transaction_id }}">
        <div class="context-summary"><b>{{ $iss->reference_no }}</b> Update the issuance details and quantities. The reference number remains unchanged.</div>
        <form method="POST" action="{{ route('issuance.update', $iss) }}" class="stack">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="field"><label class="req">Date</label><input type="date" name="date" value="{{ optional($iss->date)->format('Y-m-d') }}" required></div>
                <div class="field"><label class="req">Employee no.</label><input type="text" name="employee_no" value="{{ $iss->employee_no }}" required></div>
                <div class="field"><label class="req">Employee name</label><input type="text" name="employee_name" value="{{ $iss->employee_name }}" required></div>
                <div class="field"><label>Department</label><input type="text" name="department" value="{{ $iss->department }}"></div>
                <div class="field"><label>Supervisor</label><input type="text" name="employee_supervisor" value="{{ $iss->employee_supervisor }}"></div>
                <div class="field span-2"><label class="req">Chief complaint</label><input type="text" name="chief_complaint" value="{{ $iss->chief_complaint }}" required></div>
                <div class="field">
                    <label class="req">Disposition</label>
                    <select name="disposition" required>
                        @foreach(['Returned to work','Sent home','Referred to hospital'] as $d)
                            <option value="{{ $d }}" @selected($iss->disposition === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Remarks</label><input type="text" name="remarks" value="{{ $iss->remarks }}"></div>
            </div>
            <div class="table-wrap" style="margin-top:14px">
                <table>
                    <thead><tr><th>Item</th><th>Batch</th><th>Expiry</th><th>Quantity</th><th></th></tr></thead>
                    <tbody>
                    @foreach($iss->lines as $l)
                        @php $max = $l->batch ? ($l->batch->quantity_on_hand + $l->quantity_issued) : $l->quantity_issued; @endphp
                        <tr data-issuance-line>
                            <td><b>{{ $l->batch->item->item_name ?? '—' }}</b></td>
                            <td class="mono">{{ $l->batch->batch_no ?? '—' }}</td>
                            <td class="mono nowrap">{{ $l->batch?->expiry_date?->format('d M Y') ?? '—' }}</td>
                            <td><input class="issuance-line-qty" type="number" min="0" max="{{ $max }}" name="lines[{{ $l->line_id }}]" value="{{ $l->quantity_issued }}" style="width:100px"></td>
                            <td><button type="button" class="btn small danger remove-issuance-line">Remove</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="notice" style="margin-top:12px">Changing quantities automatically adjusts batch stock. Set a quantity to 0 or click Remove to remove that issued line.</div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save changes</button>
            </div>
        </form>
    </template>
@endforeach
@endsection

@section('scripts')
<script>
document.addEventListener('cims:modal-opened', e => {
    e.detail.root.querySelectorAll('.remove-issuance-line').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('[data-issuance-line]');
            const input = row.querySelector('.issuance-line-qty');
            input.value = 0;
            input.disabled = true;
            row.style.opacity = '.5';
        });
    });
});
</script>
@endsection
