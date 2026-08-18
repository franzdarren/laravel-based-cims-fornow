<?php $__env->startSection('title', 'Item Master'); ?>
<?php $__env->startSection('heading', 'Item Master'); ?>
<?php $__env->startSection('subheading', 'Maintain the baseline catalog for all tracked items'); ?>
<?php $__env->startSection('top-actions'); ?>
    <a href="<?php echo e(route('items.create')); ?>" class="btn primary">Add new item</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="notice"><b>Near-expiry days is one global medicine setting.</b> Every medicine uses the same value from System Settings; it cannot be changed per medicine or per batch.</div>

    <div class="card">
        <div class="card-head"><h2>Item master records</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Unit</th><th>Supplier</th><th>Reorder level</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="mono"><?php echo e($i->item_code); ?></td>
                        <td><?php echo e($i->name); ?></td>
                        <td><?php echo e(ucfirst($i->category)); ?></td>
                        <td><?php echo e($i->unit_of_measure); ?></td>
                        <td><?php echo e($i->supplier->name ?? '—'); ?></td>
                        <td class="mono"><?php echo e($i->reorder_level); ?></td>
                        <td><span class="badge <?php echo e($i->status === 'active' ? 'green' : ''); ?>"><?php echo e(ucfirst($i->status)); ?></span></td>
                        <td>
                            <div class="actions">
                                <a href="<?php echo e(route('items.edit', $i)); ?>" class="btn small">Edit</a>
                                <?php if($i->status === 'active'): ?>
                                    <form method="POST" action="<?php echo e(route('items.destroy', $i)); ?>" onsubmit="return confirm('Delete this item? It will be marked inactive; historical records are kept.');">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn small danger">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('items.reactivate', $i)); ?>">
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
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/items/index.blade.php ENDPATH**/ ?>