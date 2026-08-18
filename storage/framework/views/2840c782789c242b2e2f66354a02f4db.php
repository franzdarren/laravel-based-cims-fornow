<?php $__env->startSection('title', 'Disposals'); ?>
<?php $__env->startSection('heading', 'Disposals'); ?>
<?php $__env->startSection('subheading', 'Review posted disposal records'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-head">
        <h2>Disposed item records</h2>
        <div class="search-wrap"><input type="text" data-table-search="disposalTable" placeholder="Search disposal records"></div>
    </div>
    <div class="table-wrap">
        <table id="disposalTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Reference</th>
                <th data-sort="date">Date</th>
                <th data-sort="text">Item</th>
                <th>Batch / asset</th>
                <th data-sort="number" class="right">Quantity</th>
                <th>Reason</th>
                <th>Disposed by</th>
            </tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $disposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $__currentLoopData = $log->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $item = $l->batch?->item ?? $l->equipment?->item;
                        $qty = $l->batch ? max(0, ($l->qty_before ?? 0) - ($l->qty_after ?? 0)) : 1;
                    ?>
                    <tr>
                        <td class="mono"><?php echo e($log->reference_no); ?></td>
                        <td class="mono nowrap" data-sort-value="<?php echo e($log->transaction_datetime); ?>"><?php echo e($log->transaction_datetime->format('d M Y')); ?></td>
                        <td><?php echo e($item->item_name ?? '—'); ?></td>
                        <td class="mono"><?php echo e($l->batch->batch_no ?? $l->equipment->asset_tag ?? '—'); ?></td>
                        <td class="right mono"><?php echo e($qty); ?></td>
                        <td><?php echo e($log->reason); ?></td>
                        <td><?php echo e($log->user->fullname ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr class="empty-row"><td colspan="7" class="empty">No disposals recorded yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/disposals/index.blade.php ENDPATH**/ ?>