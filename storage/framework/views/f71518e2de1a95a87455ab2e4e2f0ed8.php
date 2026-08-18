<?php $__env->startSection('title', 'Issuance Records'); ?>
<?php $__env->startSection('heading', 'Issuance records'); ?>
<?php $__env->startSection('subheading', 'Review and edit recorded medicine and supply issuances'); ?>
<?php $__env->startSection('top-actions'); ?>
    <a href="<?php echo e(route('issuance.create')); ?>" class="btn primary">New issuance</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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
            <?php $__empty_1 = true; $__currentLoopData = $issuances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $iss): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="mono"><?php echo e($iss->reference_no); ?></td>
                    <td class="mono nowrap" data-sort-value="<?php echo e($iss->date); ?>"><?php echo e($iss->date->format('d M Y')); ?></td>
                    <td><b><?php echo e($iss->employee_name ?: '—'); ?></b><div class="muted small mono"><?php echo e($iss->employee_no); ?></div></td>
                    <td><?php echo e($iss->department ?: '—'); ?></td>
                    <td><?php echo e($iss->chief_complaint); ?><div class="muted small"><?php echo e($iss->disposition); ?></div></td>
                    <td class="small">
                        <?php $__currentLoopData = $iss->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($l->inventoryItem->name); ?> · <span class="mono"><?php echo e($l->batch->batch_number ?? '—'); ?> × <?php echo e($l->quantity); ?></span><?php if(!$loop->last): ?><br><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td><span class="badge green"><?php echo e($iss->status); ?></span></td>
                    <td><button type="button" class="btn small" data-modal-open="edit-issuance-<?php echo e($iss->id); ?>" data-modal-title="Edit issuance record">Edit</button></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr class="empty-row"><td colspan="8" class="empty">No issuance records yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__currentLoopData = $issuances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $iss): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <template id="edit-issuance-<?php echo e($iss->id); ?>">
        <div class="context-summary"><b><?php echo e($iss->reference_no); ?></b> Update the issuance details and quantities. The reference number remains unchanged.</div>
        <form method="POST" action="<?php echo e(route('issuance.update', $iss)); ?>" class="stack">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="form-grid">
                <div class="field"><label class="req">Date</label><input type="date" name="date" value="<?php echo e($iss->date->format('Y-m-d')); ?>" required></div>
                <div class="field"><label class="req">Employee no.</label><input type="text" name="employee_no" value="<?php echo e($iss->employee_no); ?>" required></div>
                <div class="field"><label class="req">Employee name</label><input type="text" name="employee_name" value="<?php echo e($iss->employee_name); ?>" required></div>
                <div class="field"><label>Department</label><input type="text" name="department" value="<?php echo e($iss->department); ?>"></div>
                <div class="field"><label>Supervisor</label><input type="text" name="employee_supervisor" value="<?php echo e($iss->employee_supervisor); ?>"></div>
                <div class="field span-2"><label class="req">Chief complaint</label><input type="text" name="chief_complaint" value="<?php echo e($iss->chief_complaint); ?>" required></div>
                <div class="field">
                    <label class="req">Disposition</label>
                    <select name="disposition" required>
                        <?php $__currentLoopData = ['Returned to work','Sent home','Referred to hospital']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($d); ?>" <?php if($iss->disposition === $d): echo 'selected'; endif; ?>><?php echo e($d); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field"><label>Remarks</label><input type="text" name="remarks" value="<?php echo e($iss->remarks); ?>"></div>
            </div>
            <div class="table-wrap" style="margin-top:14px">
                <table>
                    <thead><tr><th>Item</th><th>Batch</th><th>Expiry</th><th>Quantity</th><th></th></tr></thead>
                    <tbody>
                    <?php $__currentLoopData = $iss->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $max = $l->batch ? ($l->batch->qty_on_hand + $l->quantity) : $l->quantity; ?>
                        <tr data-issuance-line>
                            <td><b><?php echo e($l->inventoryItem->name); ?></b></td>
                            <td class="mono"><?php echo e($l->batch->batch_number ?? '—'); ?></td>
                            <td class="mono nowrap"><?php echo e($l->batch?->expiry_date?->format('d M Y') ?? '—'); ?></td>
                            <td><input class="issuance-line-qty" type="number" min="0" max="<?php echo e($max); ?>" name="lines[<?php echo e($l->id); ?>]" value="<?php echo e($l->quantity); ?>" style="width:100px"></td>
                            <td><button type="button" class="btn small danger remove-issuance-line">Remove</button></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/issuance/index.blade.php ENDPATH**/ ?>