<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('heading', 'Dashboard'); ?>
<?php $__env->startSection('subheading', 'Clinic inventory overview'); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="grid cols-4">
        <div class="card kpi shadow">
            <div class="label">Active item records</div>
            <div class="value"><?php echo e($activeItemsCount); ?></div>
            <div class="hint">Medicines, supplies, and equipment</div>
        </div>
        <div class="card kpi shadow">
            <div class="label">Near-expiry batches</div>
            <div class="value tone-amber"><?php echo e($nearExpiryBatches->count()); ?></div>
            <div class="hint">One <?php echo e(\App\Models\Setting::get('near_expiry_days', 90)); ?>-day threshold for all medicines</div>
        </div>
        <div class="card kpi shadow">
            <div class="label">Below reorder level</div>
            <div class="value tone-red"><?php echo e($lowStockItems->count()); ?></div>
            <div class="hint">Medicines and supplies needing attention</div>
        </div>
        <div class="card kpi shadow">
            <div class="label"><?php echo e($fourthCard['label']); ?></div>
            <div class="value"><?php echo e($fourthCard['value']); ?></div>
            <div class="hint"><?php echo e($fourthCard['hint']); ?></div>
        </div>
    </div>

    <div class="split">
        <div class="card">
            <div class="card-head">
                <h2>Needs attention</h2>
                <span class="sub">Automatic inventory flags</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Signal</th><th>Item</th><th>Detail</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $nearExpiryBatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="row-near">
                                <td><span class="badge amber">Near expiry</span></td>
                                <td><b><?php echo e($b->item->item_name); ?></b></td>
                                <td class="mono small"><?php echo e($b->batch_no); ?> expires <?php echo e($b->expiry_date->format('d M Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php endif; ?>
                        <?php $__currentLoopData = $lowStockItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="row-low">
                                <td><span class="badge red">Low stock</span></td>
                                <td><b><?php echo e($i->item_name); ?></b></td>
                                <td class="mono small"><?php echo e($i->stockOnHand()); ?> <?php echo e($i->uom->uom_name ?? ''); ?> on hand; reorder level <?php echo e($i->reorder_threshold); ?>

                                    <?php if($role === 'Nurse'): ?>; reorder quantity <?php echo e($i->reorder_qty); ?><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($nearExpiryBatches->isEmpty() && $lowStockItems->isEmpty()): ?>
                            <tr><td colspan="3" class="muted">Nothing currently needs attention.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2>Role workflow</h2>
                <span class="sub"><?php echo e($role); ?></span>
            </div>
            <div class="timeline">
                <?php $__currentLoopData = $workflow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="timeline-item">
                        <span class="dot"></span>
                        <div><b><?php echo e($step[0]); ?></b><div class="muted small"><?php echo e($step[1]); ?></div></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <?php if($canViewLogs): ?>
    <div class="card">
        <div class="card-head">
            <h2>Recent transactions</h2>
            <a href="<?php echo e(route('logs.index')); ?>" class="btn small">Open full log</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>User</th><th>Activity</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="mono nowrap"><?php echo e(optional($log->date)->format('d M Y')); ?></td>
                            <td><span class="badge blue"><?php echo e($log->normalizedType()); ?></span></td>
                            <td class="mono"><?php echo e($log->reference_no); ?></td>
                            <td><?php echo e($log->user->fullname ?? 'System'); ?></td>
                            <td><?php echo e($log->detail); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="muted">No activity yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/dashboard/index.blade.php ENDPATH**/ ?>