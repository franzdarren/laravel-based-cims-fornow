<div class="field">
    <label class="req">Supplier name</label>
    <input type="text" name="supplier_name" value="{{ old('supplier_name', $supplier->supplier_name ?? '') }}" required>
</div>
<div class="field">
    <label>Contact person</label>
    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}">
</div>
<div class="field">
    <label>Contact number</label>
    <input type="text" name="contact_no" value="{{ old('contact_no', $supplier->contact_no ?? '') }}">
</div>
<div class="field">
    <label>Address</label>
    <input type="text" name="address" value="{{ old('address', $supplier->address ?? '') }}">
</div>
@if($supplier)
<div class="field">
    <label class="req">Status</label>
    <select name="status" required>
        <option value="active" @selected(old('status', $supplier->status) === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $supplier->status) === 'inactive')>Inactive</option>
    </select>
</div>
@endif
