<div class="form-grid">
    <div class="field">
        <label class="req">Item code</label>
        <input type="text" name="item_code" value="{{ old('item_code', $item->item_code ?? '') }}" required>
    </div>
    <div class="field">
        <label class="req">Item name</label>
        <input type="text" name="item_name" value="{{ old('item_name', $item->item_name ?? '') }}" required>
    </div>
    <div class="field">
        <label class="req">Category</label>
        <select name="item_category" required>
            @foreach(['MEDICINE' => 'Medicine', 'SUPPLY' => 'Supply', 'EQUIPMENT' => 'Equipment'] as $val => $label)
                <option value="{{ $val }}" @selected(old('item_category', $item->item_category ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label class="req">Unit of measure</label>
        <select name="uom_id" required>
            <option value="">— Select —</option>
            @foreach($uoms as $u)
                <option value="{{ $u->uom_id }}" @selected(old('uom_id', $item->uom_id ?? '') == $u->uom_id)>{{ $u->uom_name }}</option>
            @endforeach
        </select>
        <div class="muted small">Need a new unit? Add it under System Settings → Units of Measurement.</div>
    </div>
    <div class="field">
        <label>Preferred supplier</label>
        <select name="supplier_id">
            <option value="">— None —</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->supplier_id }}" @selected(old('supplier_id', $item->supplier_id ?? '') == $s->supplier_id)>{{ $s->supplier_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label class="req">Reorder level</label>
        <input type="number" min="0" name="reorder_threshold" value="{{ old('reorder_threshold', $item->reorder_threshold ?? 0) }}" required>
    </div>
    <div class="field">
        <label class="req">Reorder quantity</label>
        <input type="number" min="0" name="reorder_qty" value="{{ old('reorder_qty', $item->reorder_qty ?? 0) }}" required>
    </div>
</div>
