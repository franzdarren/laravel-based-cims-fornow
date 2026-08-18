<?php $__env->startSection('title', 'Approvals'); ?>
<?php $__env->startSection('heading', 'Approvals'); ?>
<?php $__env->startSection('subheading', 'Approve or return nurse-submitted receiving requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-head"><h2>Pending receiving approvals</h2><span class="badge amber"><?php echo e($pending->count()); ?></span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Reference</th><th>Supplier</th><th>Date</th><th>Contents</th><th></th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="mono"><?php echo e($r->ref_no); ?></td>
                    <td><?php echo e($r->supplier->supplier_name); ?></td>
                    <td class="mono nowrap"><?php echo e(optional($r->date_received)->format('d M Y')); ?></td>
                    <td>
                        <?php $__currentLoopData = $r->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($l->item->item_name); ?> × <span class="mono"><?php echo e($l->quantity); ?></span><?php if(!$loop->last): ?><br><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn small" data-modal-open="review-receiving-<?php echo e($r->receiving_transaction_id); ?>" data-modal-title="Review <?php echo e($r->ref_no); ?>">Review</button>
                            <form method="POST" action="<?php echo e(route('approvals.approve', $r)); ?>" onsubmit="return confirm('Approve this delivery and post it to inventory?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn small primary">Approve</button>
                            </form>
                            <button type="button" class="btn small danger" data-modal-open="return-receiving-<?php echo e($r->receiving_transaction_id); ?>" data-modal-title="Return request to Nurse">Return</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="empty">No receiving requests are awaiting approval.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <template id="review-receiving-<?php echo e($r->receiving_transaction_id); ?>">
        <div class="context-summary">
            <b>Reference number: <span class="mono"><?php echo e($r->ref_no); ?></span></b>
            <div><?php echo e($r->supplier->supplier_name); ?> · <?php echo e(optional($r->date_received)->format('d M Y')); ?> · Encoded by <?php echo e($r->receivedBy->fullname); ?></div>
            <div style="margin-top:7px"><b style="display:inline">Remarks:</b> <?php echo e($r->remarks ?: '—'); ?></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th class="right">Quantity</th><th>Details</th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $r->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $i = $l->item; $eq = $i->item_category === 'EQUIPMENT'; ?>
                    <tr>
                        <td><?php echo e($i->item_name); ?></td>
                        <td class="mono right"><?php echo e($l->quantity); ?></td>
                        <td class="mono small">
                            <?php if($eq): ?>
                                Brand: <?php echo e($l->brand ?: '—'); ?><br>Model: <?php echo e($l->model ?: '—'); ?><br>Serial number: <?php echo e($l->serial_number ?: '—'); ?><br>Asset tag: <?php echo e($l->asset_tag ?: '—'); ?><br>Location: <?php echo e($l->location->location_name ?? '—'); ?>

                            <?php else: ?>
                                Brand: <?php echo e($l->brand ?: '—'); ?><br>Batch number: <?php echo e($l->batch_no ?: '—'); ?><br>Expiry: <?php echo e($l->expiry_date?->format('d M Y') ?: '—'); ?><br>Location: <?php echo e($l->location->location_name ?? '—'); ?>

                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="modal-actions"><button type="button" class="btn" onclick="CIMS.modal.close()">Close</button></div>
    </template>
    <template id="return-receiving-<?php echo e($r->receiving_transaction_id); ?>">
        <div class="context-summary"><b><?php echo e($r->ref_no); ?></b> The request will remain visible in its record history.</div>
        <form method="POST" action="<?php echo e(route('approvals.return', $r)); ?>" class="stack">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label class="req">Return reason</label>
                <textarea name="reason" maxlength="150" required></textarea>
                <div class="muted small">Maximum 150 characters</div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Back</button>
                <button type="submit" class="btn primary">Return request</button>
            </div>
        </form>
    </template>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/approvals/index.blade.php ENDPATH**/ ?>