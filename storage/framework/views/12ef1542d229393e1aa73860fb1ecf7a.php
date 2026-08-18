<?php $__env->startSection('title', 'Receiving'); ?>
<?php $__env->startSection('heading', 'Encode receiving transaction'); ?>
<?php $__env->startSection('subheading', 'Encode new receiving transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="card">
        <div class="card-head"><h2>Delivery details</h2></div>
        <form method="POST" action="<?php echo e(route('receiving.lines.add')); ?>" class="stack">
            <?php echo csrf_field(); ?>
            <div class="form-grid">
                <div class="field">
                    <label class="req">Supplier</label>
                    <select name="supplier_id" required>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->id); ?>" <?php if(($header['supplier_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field">
                    <label class="req">Delivery reference</label>
                    <input type="text" class="mono" name="reference_no" value="<?php echo e($header['reference_no'] ?? ''); ?>" placeholder="e.g., DR-58005" required>
                </div>
                <div class="field">
                    <label class="req">Date received</label>
                    <input type="date" name="date_received" value="<?php echo e($header['date_received'] ?? now()->toDateString()); ?>" required>
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <input type="text" name="remarks" maxlength="150" value="<?php echo e($header['remarks'] ?? ''); ?>">
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
                            <?php $__currentLoopData = ['medicine' => 'Medicine', 'equipment' => 'Equipment', 'supply' => 'Supply']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <optgroup label="<?php echo e($label); ?>">
                                    <?php $__currentLoopData = $items->where('category', $cat); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($i->id); ?>" data-category="<?php echo e($i->category); ?>"><?php echo e($i->item_code); ?> · <?php echo e($i->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </optgroup>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($loc); ?>"><?php echo e($loc); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field span-4"><button type="submit" class="btn primary">Add delivery line</button></div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Delivery lines added so far</h2>
            <?php if(count($lines)): ?>
                <form method="POST" action="<?php echo e(route('receiving.draft.clear')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn small">Clear draft</button>
                </form>
            <?php endif; ?>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Category</th><th>Details</th><th class="right">Quantity</th><th></th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($l['item_name']); ?></td>
                        <td><?php echo e(ucfirst($l['category'])); ?></td>
                        <td class="mono small">
                            <?php if($l['category'] === 'equipment'): ?>
                                Brand: <?php echo e($l['brand'] ?? '—'); ?> · Model: <?php echo e($l['model'] ?? '—'); ?> · Asset tag: <?php echo e($l['asset_tag'] ?? '—'); ?> · Serial: <?php echo e($l['serial_number'] ?? '—'); ?> · Location: <?php echo e($l['location'] ?? '—'); ?>

                            <?php else: ?>
                                Brand: <?php echo e($l['brand'] ?? '—'); ?> · Batch: <?php echo e($l['batch_number'] ?? '—'); ?> · Expiry: <?php echo e($l['expiry_date'] ?? '—'); ?> · Location: <?php echo e($l['location'] ?? '—'); ?>

                            <?php endif; ?>
                        </td>
                        <td class="right mono"><?php echo e($l['quantity']); ?></td>
                        <td>
                            <form method="POST" action="<?php echo e(route('receiving.lines.remove', $idx)); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn small danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr class="empty-row"><td colspan="5" class="empty">Add one or more delivery lines.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(count($lines)): ?>
        <form method="POST" action="<?php echo e(route('receiving.store')); ?>" style="margin-top:14px">
            <?php echo csrf_field(); ?>
            <div class="actions">
                <button type="submit" class="btn primary">Submit for approval</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('itemSelect');
    const qty = document.getElementById('qtyInput');
    const sync = () => {
        const opt = select.options[select.selectedIndex];
        const isEquip = opt && opt.dataset.category === 'equipment';
        document.querySelectorAll('.batch-field').forEach(el => el.style.display = isEquip ? 'none' : 'grid');
        document.querySelectorAll('.equip-field').forEach(el => el.style.display = isEquip ? 'grid' : 'none');
        if (isEquip) { qty.value = 1; qty.setAttribute('max', '1'); } else { qty.removeAttribute('max'); }
    };
    select.addEventListener('change', sync);
    sync();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/receiving/create.blade.php ENDPATH**/ ?>