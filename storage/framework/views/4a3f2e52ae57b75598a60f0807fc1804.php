<?php $__env->startSection('title', 'Review '.$receiving->reference_no); ?>
<?php $__env->startSection('heading', 'Review '.$receiving->reference_no); ?>
<?php $__env->startSection('subheading', 'Approvals'); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="card">
        <div class="context-summary">
            <b>Reference number: <span class="mono"><?php echo e($receiving->reference_no); ?></span></b><br>
            <?php echo e($receiving->supplier->name); ?> · <?php echo e($receiving->date_received->format('d M Y')); ?> · Encoded by <?php echo e($receiving->receivedBy->full_name); ?>

            <?php if($receiving->remarks): ?>
                <br><span class="muted small">Remarks: <?php echo e($receiving->remarks); ?></span>
            <?php endif; ?>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Details</th><th>Qty</th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $receiving->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($l->inventoryItem->name); ?></td>
                        <td class="mono small">
                            <?php if($l->inventoryItem->category === 'equipment'): ?>
                                <?php echo e($l->model); ?> @ <?php echo e($l->location); ?>

                            <?php else: ?>
                                <?php echo e($l->batch_number); ?> <?php if($l->expiry_date): ?> exp <?php echo e($l->expiry_date->format('d M Y')); ?> <?php endif; ?>
                            <?php endif; ?>
                            <?php if($l->brand): ?> · <?php echo e($l->brand); ?> <?php endif; ?>
                        </td>
                        <td class="mono right"><?php echo e($l->quantity); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <hr>

        <div class="actions">
            <form method="POST" action="<?php echo e(route('approvals.approve', $receiving)); ?>" onsubmit="return confirm('Approve this delivery and post it to inventory?');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn primary">Approve</button>
            </form>
            <form method="POST" action="<?php echo e(route('approvals.return', $receiving)); ?>" onsubmit="return attachReason(this)">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="reason" class="return-reason-field">
                <button type="submit" class="btn danger">Return</button>
            </form>
            <a href="<?php echo e(route('approvals.index')); ?>" class="btn">Back</a>
        </div>
    </div>
</div>

<script>
function attachReason(form){
    var reason = prompt('Reason for returning this request to the Nurse:');
    if(!reason){ return false; }
    form.querySelector('.return-reason-field').value = reason;
    return true;
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/approvals/show.blade.php ENDPATH**/ ?>