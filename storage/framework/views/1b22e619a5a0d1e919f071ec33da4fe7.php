<?php $__env->startSection('title', 'Batches'); ?>
<?php $__env->startSection('heading', 'Batches'); ?>
<?php $__env->startSection('subheading', 'View stock batches and record disposals'); ?>

<?php $__env->startSection('content'); ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr data-status="<?php echo e($b->batch_status); ?>" class="<?php echo e($b->isNearExpiry() ? 'row-near' : ''); ?>">
                        <td><b><?php echo e($b->item->item_name); ?></b><div class="muted small"><?php echo e($b->item->item_code); ?></div></td>
                        <td class="mono"><?php echo e($b->batch_no); ?></td>
                        <td><?php echo e($b->brand ?: '—'); ?></td>
                        <td class="mono nowrap" data-sort-value="<?php echo e($b->expiry_date); ?>"><?php echo e($b->expiry_date?->format('d M Y') ?? '—'); ?></td>
                        <td class="right mono"><?php echo e($b->quantity_on_hand); ?></td>
                        <td><?php echo e($b->item->uom->uom_name ?? '—'); ?></td>
                        <td>
                            <?php $tone = match($b->batch_status){'ACTIVE'=>'green','DISPOSED'=>'red','FOR_DISPOSAL'=>'amber',default=>''}; ?>
                            <span class="badge <?php echo e($tone); ?>"><?php echo e(ucfirst(strtolower($b->batch_status))); ?></span>
                            <?php if($b->isNearExpiry()): ?><span class="badge amber">Near expiry</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if(auth()->user()->isRole('Nurse')): ?>
                                <div class="actions">
                                    <button type="button" class="btn small" data-modal-open="edit-batch-<?php echo e($b->batch_id); ?>" data-modal-title="Edit batch record">Edit</button>
                                    <?php if($b->batch_status === 'ACTIVE' && $b->quantity_on_hand > 0): ?>
                                        <button type="button" class="btn small danger" data-modal-open="dispose-batch-<?php echo e($b->batch_id); ?>" data-modal-title="Record batch disposal">Dispose</button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="muted small">View only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr class="empty-row"><td colspan="8" class="empty">No batches found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(auth()->user()->isRole('Nurse')): ?>
<?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <template id="edit-batch-<?php echo e($b->batch_id); ?>">
        <div class="context-summary"><b><?php echo e($b->item->item_name); ?></b> <span class="mono"><?php echo e($b->batch_no); ?></span> · Current on hand: <?php echo e($b->quantity_on_hand); ?> <?php echo e($b->item->uom->uom_name ?? ''); ?></div>
        <form method="POST" action="<?php echo e(route('batches.update', $b)); ?>" class="stack">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="form-grid">
                <div class="field"><label class="req">Batch number</label><input type="text" class="mono" name="batch_number" value="<?php echo e($b->batch_no); ?>" required></div>
                <div class="field"><label>Brand</label><input type="text" name="brand" value="<?php echo e($b->brand); ?>"></div>
                <div class="field"><label>Expiry date</label><input type="date" name="expiry_date" value="<?php echo e(optional($b->expiry_date)->format('Y-m-d')); ?>"></div>
                <div class="field"><label class="req">On-hand quantity</label><input type="number" min="0" name="qty_on_hand" value="<?php echo e($b->quantity_on_hand); ?>" required></div>
                <div class="field span-4"><label class="req">Reason for edit</label><input type="text" name="reason" placeholder="e.g., Corrected physical count or encoded batch details" required></div>
            </div>
            <div class="notice" style="margin-top:12px">Changes are saved immediately and recorded in the transaction log. Supervisor approval is not required.</div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save changes</button>
            </div>
        </form>
    </template>
    <?php if($b->batch_status === 'ACTIVE' && $b->quantity_on_hand > 0): ?>
        <template id="dispose-batch-<?php echo e($b->batch_id); ?>">
            <div class="context-summary"><b><?php echo e($b->item->item_name); ?></b> <span class="mono"><?php echo e($b->batch_no); ?></span> · <?php echo e($b->quantity_on_hand); ?> <?php echo e($b->item->uom->uom_name ?? ''); ?> on hand</div>
            <form method="POST" action="<?php echo e(route('batches.dispose', $b)); ?>" class="stack">
                <?php echo csrf_field(); ?>
                <div class="form-grid">
                    <div class="field"><label class="req">Quantity to dispose</label><input type="number" min="1" max="<?php echo e($b->quantity_on_hand); ?>" name="quantity" value="1" required></div>
                    <div class="field span-3">
                        <label class="req">Disposal reason</label>
                        <select name="reason" required>
                            <?php $__currentLoopData = ['Expired','Damaged','Contaminated','Packaging issue','Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($r); ?>"><?php echo e($r); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/batches/index.blade.php ENDPATH**/ ?>