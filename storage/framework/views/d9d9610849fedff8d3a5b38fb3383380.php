<?php $__env->startSection('title', 'Roles'); ?>
<?php $__env->startSection('heading', 'Roles'); ?>
<?php $__env->startSection('subheading', 'Define which pages and actions each role may use'); ?>
<?php $__env->startSection('top-actions'); ?>
    <a href="<?php echo e(route('roles.create')); ?>" class="btn primary">Add role</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Description</th><th>Active users</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><b><?php echo e($r->name); ?></b></td>
                    <td class="wrap"><?php echo e($r->description); ?></td>
                    <td class="mono"><?php echo e($r->users_count); ?></td>
                    <td><span class="badge <?php echo e($r->status === 'active' ? 'green' : ''); ?>"><?php echo e(ucfirst($r->status)); ?></span></td>
                    <td><a href="<?php echo e(route('roles.edit', $r)); ?>" class="btn small">Edit</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/roles/index.blade.php ENDPATH**/ ?>