@extends('layouts.app')
@section('title', 'Issuance')
@section('heading', 'Record medicine or supply issuance')
@section('subheading', 'Issue medicines and supplies using FEFO')
@section('top-actions')
    <span class="badge green">FEFO enabled</span>
@endsection

@section('content')
<div class="stack">
    <div class="card">
        <div class="card-head"><h2>Visit details</h2></div>
        <form method="POST" action="{{ route('issuance.lines.add') }}" class="stack">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label class="req">Employee no.</label>
                    <input type="text" name="employee_no" placeholder="EMP-0000" value="{{ $header['employee_no'] ?? '' }}" required>
                </div>
                <div class="field">
                    <label class="req">Employee name</label>
                    <input type="text" name="employee_name" placeholder="Employee full name" value="{{ $header['employee_name'] ?? '' }}" required>
                </div>
                <div class="field">
                    <label>Department</label>
                    <input type="text" name="department" value="{{ $header['department'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Supervisor</label>
                    <input type="text" name="employee_supervisor" value="{{ $header['employee_supervisor'] ?? '' }}">
                </div>
                <div class="field">
                    <label class="req">Disposition</label>
                    <select name="disposition" required>
                        @foreach(['Returned to work','Sent home','Referred to hospital'] as $d)
                            <option value="{{ $d }}" @selected(($header['disposition'] ?? '') === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field span-2">
                    <label class="req">Chief complaint</label>
                    <input type="text" name="chief_complaint" value="{{ $header['chief_complaint'] ?? '' }}" required>
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <input type="text" name="remarks" value="{{ $header['remarks'] ?? '' }}">
                </div>
            </div>

            <hr>

            <div class="form-grid">
                <div class="field span-2">
                    <label class="req">Item</label>
                    <select name="item_id" id="issItem" required>
                        @foreach($items as $i)
                            <option value="{{ $i->item_id }}" data-unit="{{ $i->uom->uom_name ?? '' }}">{{ $i->item_code }} · {{ $i->item_name }} ({{ $i->stockOnHand() }} {{ $i->uom->uom_name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="req">Quantity</label>
                    <input type="number" min="1" name="quantity" id="issQty" value="1" required>
                </div>
                <div class="field span-4" id="allocationPreview">
                    <div class="allocation-panel">
                        <span class="allocation-title">FEFO quantity allocation</span>
                        <div class="allocation-empty">Loading…</div>
                    </div>
                </div>
                <div class="field span-4"><button type="submit" class="btn primary">Add item to issuance</button></div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Items added so far</h2>
            @if(count($lines))
                <form method="POST" action="{{ route('issuance.draft.clear') }}">
                    @csrf
                    <button type="submit" class="btn small">Clear draft</button>
                </form>
            @endif
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Batch</th><th>Expiry</th><th class="right">Quantity</th><th></th></tr></thead>
                <tbody>
                @forelse($lines as $idx => $l)
                    @foreach($l['allocation_preview'] as $a)
                        <tr>
                            <td>{{ $l['item_name'] }}</td>
                            <td class="mono">{{ $a['batch_number'] }}</td>
                            <td class="mono nowrap">{{ $a['expiry_date'] ?? '—' }}</td>
                            <td class="right mono">{{ $a['qty'] }}</td>
                            @if($loop->first)
                                <td rowspan="{{ count($l['allocation_preview']) }}">
                                    <form method="POST" action="{{ route('issuance.lines.remove', $idx) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn small danger">Remove</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr class="empty-row"><td colspan="5" class="empty">Add one or more items to the issuance.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(count($lines))
        <div class="actions" style="margin-top:12px">
            <form method="POST" action="{{ route('issuance.store') }}">
                @csrf
                <button type="submit" class="btn primary">Record issuance</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const itemSelect = document.getElementById('issItem');
    const qtyInput = document.getElementById('issQty');
    const preview = document.getElementById('allocationPreview');
    const previewUrl = @json(route('issuance.allocation-preview'));

    const swatchClass = n => n === 0 ? '' : n === 1 ? 'alt' : 'alt2';

    async function refresh() {
        const itemId = itemSelect.value;
        const qty = Number(qtyInput.value) || 0;
        const opt = itemSelect.options[itemSelect.selectedIndex];
        const unit = opt ? opt.dataset.unit : '';
        if (!itemId) {
            preview.innerHTML = '<div class="allocation-panel"><span class="allocation-title">FEFO quantity allocation</span><div class="allocation-empty">Select an item to preview how the requested quantity will be allocated.</div></div>';
            return;
        }
        const res = await fetch(previewUrl + '?item_id=' + encodeURIComponent(itemId) + '&quantity=' + encodeURIComponent(qty));
        const data = await res.json();
        const requested = Math.max(qty, 1);
        let meter = '';
        let legend = '';
        data.rows.forEach((r, n) => {
            meter += `<span class="allocation-segment ${swatchClass(n % 3)}" style="width:${Math.max(0, (r.qty / requested) * 100)}%"></span>`;
            legend += `<div class="allocation-row"><span class="swatch"></span><span><b>${r.batch_number}</b> · expires ${r.expiry_date || '—'}</span><span class="mono">${r.qty}</span></div>`;
        });
        if (data.short) {
            meter += `<span class="allocation-segment short" style="width:${Math.max(0, (data.short / requested) * 100)}%"></span>`;
            legend += `<div class="allocation-row"><span class="swatch" style="background:#ad3037"></span><span class="tone-red"><b>Insufficient stock</b></span><span class="mono tone-red">${data.short}</span></div>`;
        }
        preview.innerHTML = `<div class="allocation-panel">
            <div class="allocation-header">
                <div><label class="allocation-title">FEFO quantity allocation</label><div class="quantity-summary">Requested: <b>${qty}</b> ${unit}${qty === 1 ? '' : 's'} · Available: <b>${data.available}</b></div></div>
                <div class="allocation-help">Oldest-expiring active batches are used first</div>
            </div>
            ${qty > 0 ? `<div class="allocation-meter">${meter}</div><div class="allocation-legend">${legend}</div>` : '<div class="allocation-empty">Enter a quantity to see the FEFO allocation.</div>'}
        </div>`;
    }

    itemSelect.addEventListener('change', refresh);
    qtyInput.addEventListener('input', refresh);
    refresh();
});
</script>
@endsection
