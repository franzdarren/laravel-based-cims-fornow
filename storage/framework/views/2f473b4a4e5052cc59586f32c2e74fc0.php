<?php $__env->startSection('title', 'System Settings'); ?>
<?php $__env->startSection('heading', 'System Settings'); ?>
<?php $__env->startSection('subheading', 'The rules that quietly run the whole operation'); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="card" style="max-width:520px">
        <div class="card-head"><h2>Global near-expiry threshold</h2></div>
        <p class="muted small">Every medicine batch uses this one value. Changing it updates the Dashboard and Batches flags for all medicines immediately — it cannot be set per item or per batch.</p>
        <form method="POST" action="<?php echo e(route('settings.global')); ?>" class="stack">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="field">
                <label class="req">Near-expiry days</label>
                <input type="number" min="1" max="365" name="near_expiry_days" value="<?php echo e(old('near_expiry_days', $nearExpiryDays)); ?>" required>
            </div>
            <div class="actions"><button type="submit" class="btn primary">Save global setting</button></div>
        </form>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Reorder levels by item</h2>
            <span class="sub">Per-item — set individually below</span>
        </div>

        
        <?php $__currentLoopData = $reorderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <form id="reorder-form-<?php echo e($i->id); ?>" method="POST" action="<?php echo e(route('settings.reorder', $i)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            </form>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Reorder level</th><th>Reorder quantity</th><th></th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $reorderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($i->name); ?> <span class="mono small muted">(<?php echo e($i->item_code); ?>)</span></td>
                        <td><input form="reorder-form-<?php echo e($i->id); ?>" type="number" min="0" name="reorder_level" value="<?php echo e($i->reorder_level); ?>" style="width:90px"></td>
                        <td><input form="reorder-form-<?php echo e($i->id); ?>" type="number" min="0" name="reorder_quantity" value="<?php echo e($i->reorder_quantity); ?>" style="width:90px"></td>
                        <td><button form="reorder-form-<?php echo e($i->id); ?>" type="submit" class="btn small">Save</button></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/settings/edit.blade.php ENDPATH**/ ?>