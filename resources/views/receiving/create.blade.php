@extends('layouts.app')
@section('title', 'Receiving')
@section('heading', 'Encode receiving transaction')
@section('subheading', 'Encode new receiving transactions')

@section('content')
<div class="stack">
    <div class="card">
        <div class="card-head"><h2>Delivery details</h2></div>
        <form method="POST" action="{{ route('receiving.lines.add') }}" class="stack">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label class="req">Supplier</label>
                    <select name="supplier_id" required>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->supplier_id }}" @selected(($header['supplier_id'] ?? null) == $s->supplier_id)>{{ $s->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="req">Delivery reference</label>
                    <input type="text" class="mono" name="reference_no" value="{{ $header['reference_no'] ?? '' }}" placeholder="e.g., DR-58005" required>
                </div>
                <div class="field">
                    <label class="req">Date received</label>
                    <input type="date" name="date_received" value="{{ $header['date_received'] ?? now()->toDateString() }}" required>
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <input type="text" name="remarks" maxlength="150" value="{{ $header['remarks'] ?? '' }}">
                    <div class="muted small">Maximum 150 characters</div>
                </div>
            </div>

            <hr>

            <div class="form-grid">
                <div class="field span-2">
                    <label class="req">Item</label>
                    <div class="item-combobox" data-combobox>
                        <input type="text" placeholder="Search or select an item" autocomplete="off">
                        <select name="item_id" id="itemSelect" required>
                            <option value=""></option>
                            @foreach(['MEDICINE' => 'Medicine', 'EQUIPMENT' => 'Equipment', 'SUPPLY' => 'Supply'] as $cat => $label)
                                <optgroup label="{{ $label }}">
                                    @foreach($items->where('item_category', $cat) as $i)
                                        <option value="{{ $i->item_id }}" data-category="{{ $i->item_category }}">{{ $i->item_code }} · {{ $i->item_name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div class="item-combo-menu"></div>
                    </div>
                    <div class="muted small">Type directly in the field to search. Items are grouped by category.</div>
                </div>
                <div class="field">
                    <label class="req">Quantity</label>
                    <input type="number" min="1" name="quantity" id="qtyInput" value="1" required>
                </div>
                <div class="field">
                    <label>Brand</label>
                    <input type="text" name="brand">
                </div>
                <div class="field batch-field">
                    <label>Batch number <span class="muted">(optional)</span></label>
                    <input type="text" class="mono" name="batch_number">
                </div>
                <div class="field batch-field">
                    <label>Expiry date</label>
                    <input type="date" name="expiry_date">
                </div>
                <div class="field equip-field" style="display:none">
                    <label>Equipment model</label>
                    <input type="text" name="model">
                </div>
                <div class="field equip-field" style="display:none">
                    <label class="req">Serial number</label>
                    <input type="text" class="mono" name="serial_number">
                </div>
                <div class="field equip-field" style="display:none">
                    <label class="req">Asset tag</label>
                    <input type="text" class="mono" name="asset_tag">
                </div>
                <div class="field">
                    <label class="req">Location</label>
                    <select name="location" required>
                        <option value="">Select location</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field span-4"><button type="submit" class="btn primary">Add delivery line</button></div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Delivery lines added so far</h2>
            @if(count($lines))
                <form method="POST" action="{{ route('receiving.draft.clear') }}">
                    @csrf
                    <button type="submit" class="btn small">Clear draft</button>
                </form>
            @endif
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Category</th><th>Details</th><th class="right">Quantity</th><th></th></tr></thead>
                <tbody>
                @forelse($lines as $idx => $l)
                    <tr>
                        <td>{{ $l['item_name'] }}</td>
                        <td>{{ ucfirst(strtolower($l['category'])) }}</td>
                        <td class="mono small">
                            @if($l['category'] === 'EQUIPMENT')
                                Brand: {{ $l['brand'] ?? '—' }} · Model: {{ $l['model'] ?? '—' }} · Asset tag: {{ $l['asset_tag'] ?? '—' }} · Serial: {{ $l['serial_number'] ?? '—' }} · Location: {{ $l['location'] ?? '—' }}
                            @else
                                Brand: {{ $l['brand'] ?? '—' }} · Batch: {{ $l['batch_number'] ?? '—' }} · Expiry: {{ $l['expiry_date'] ?? '—' }} · Location: {{ $l['location'] ?? '—' }}
                            @endif
                        </td>
                        <td class="right mono">{{ $l['quantity'] }}</td>
                        <td>
                            <form method="POST" action="{{ route('receiving.lines.remove', $idx) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn small danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="5" class="empty">Add one or more delivery lines.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(count($lines))
        <form method="POST" action="{{ route('receiving.store') }}" style="margin-top:14px">
            @csrf
            <div class="actions">
                <button type="submit" class="btn primary">Submit for approval</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('itemSelect');
    const qty = document.getElementById('qtyInput');
    const sync = () => {
        const opt = select.options[select.selectedIndex];
        const isEquip = opt && opt.dataset.category === 'EQUIPMENT';
        document.querySelectorAll('.batch-field').forEach(el => el.style.display = isEquip ? 'none' : 'grid');
        document.querySelectorAll('.equip-field').forEach(el => el.style.display = isEquip ? 'grid' : 'none');
        if (isEquip) { qty.value = 1; qty.setAttribute('max', '1'); } else { qty.removeAttribute('max'); }
    };
    select.addEventListener('change', sync);
    sync();
});
</script>
@endsection
