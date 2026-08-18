<?php $__env->startSection('title', 'Transaction Log'); ?>
<?php $__env->startSection('heading', 'Transaction log'); ?>
<?php $__env->startSection('subheading', 'Immutable audit trail of system activity'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-head">
        <h2>Transaction log</h2>
        <form method="GET" action="<?php echo e(route('logs.index')); ?>" class="search-wrap">
            <input type="text" name="q" value="<?php echo e($search); ?>" placeholder="Search type, reference, user, or activity">
            <select name="type" onchange="this.form.submit()">
                <option value="">All transaction types</option>
                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t); ?>" <?php if($selectedType === $t): echo 'selected'; endif; ?>><?php echo e(ucfirst(strtolower($t))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="btn small">Search</button>
        </form>
    </div>

    <div class="table-wrap">
        <table id="logTable">
            <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>User</th><th class="wrap">Activity</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="mono nowrap"><?php echo e(optional($log->date)->format('d M Y')); ?></td>
                    <td><span class="badge blue"><?php echo e($log->normalizedType()); ?></span></td>
                    <td class="mono"><?php echo e($log->reference_no); ?></td>
                    <td><?php echo e($log->user->fullname ?? 'System'); ?></td>
                    <td class="wrap"><?php echo e($log->detail); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="empty">No matching activity found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php echo e($logs->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/logs/index.blade.php ENDPATH**/ ?>