<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('heading', 'Users'); ?>
<?php $__env->startSection('subheading', 'Create, edit, and manage staff accounts'); ?>
<?php $__env->startSection('top-actions'); ?>
    <a href="<?php echo e(route('users.create')); ?>" class="btn primary">Create new user</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Full name</th><th>Username</th><th>Role</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($u->full_name); ?></td>
                    <td class="mono"><?php echo e($u->username); ?></td>
                    <td><?php echo e($u->role->name); ?></td>
                    <td><span class="badge <?php echo e($u->status === 'active' ? 'green' : ''); ?>"><?php echo e(ucfirst($u->status)); ?></span></td>
                    <td>
                        <div class="actions">
                            <a href="<?php echo e(route('users.edit', $u)); ?>" class="btn small">Edit</a>
                            <?php if($u->status === 'active'): ?>
                                <form method="POST" action="<?php echo e(route('users.deactivate', $u)); ?>" onsubmit="return confirm('Deactivate this account?');">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn small danger">Deactivate</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?php echo e(route('users.reactivate', $u)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn small primary">Reactivate</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/users/index.blade.php ENDPATH**/ ?>