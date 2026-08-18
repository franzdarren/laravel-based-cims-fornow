<div class="field">
    <label class="req">Supplier name</label>
    <input type="text" name="name" value="<?php echo e(old('name', $supplier->name ?? '')); ?>" required>
</div>
<div class="field">
    <label>Contact person</label>
    <input type="text" name="contact_person" value="<?php echo e(old('contact_person', $supplier->contact_person ?? '')); ?>">
</div>
<div class="field">
    <label>Contact number</label>
    <input type="text" name="contact_number" value="<?php echo e(old('contact_number', $supplier->contact_number ?? '')); ?>">
</div>
<div class="field">
    <label>Address</label>
    <input type="text" name="address" value="<?php echo e(old('address', $supplier->address ?? '')); ?>">
</div>
<?php if($supplier): ?>
<div class="field">
    <label class="req">Status</label>
    <select name="status" required>
        <option value="active" <?php if(old('status', $supplier->status) === 'active'): echo 'selected'; endif; ?>>Active</option>
        <option value="inactive" <?php if(old('status', $supplier->status) === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
    </select>
</div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\cims\resources\views/suppliers/_form.blade.php ENDPATH**/ ?>