<?php $__env->startSection('title', 'Edit Batch'); ?>
<?php $__env->startSection('heading', 'Edit batch'); ?>
<?php $__env->startSection('subheading', $batch->inventoryItem->name.' — '.$batch->batch_number); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="card" style="max-width:600px">
        <div class="card-head"><h2>Batch details</h2></div>
        <form method="POST" action="<?php echo e(route('batches.update', $batch)); ?>" class="stack">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="field">
                <label class="req">Batch number</label>
                <input type="text" name="batch_number" value="<?php echo e(old('batch_number', $batch->batch_number)); ?>" required>
            </div>
            <div class="field">
                <label>Brand</label>
                <input type="text" name="brand" value="<?php echo e(old('brand', $batch->brand)); ?>">
            </div>
            <div class="field">
                <label>Expiry date</label>
                <input type="date" name="expiry_date" value="<?php echo e(old('expiry_date', optional($batch->expiry_date)->format('Y-m-d'))); ?>">
            </div>
            <div class="field">
                <label class="req">Quantity on hand</label>
                <input type="number" min="0" name="qty_on_hand" value="<?php echo e(old('qty_on_hand', $batch->qty_on_hand)); ?>" required>
            </div>
            <div class="field">
                <label class="req">Reason for this edit</label>
                <input type="text" name="reason" placeholder="e.g. Corrected a typo in the batch number" required>
            </div>
            <div class="actions">
                <button type="submit" class="btn primary">Save changes</button>
                <a href="<?php echo e(route('batches.index')); ?>" class="btn">Cancel</a>
            </div>
        </form>
    </div>

    <?php if($batch->status === 'active' && $batch->qty_on_hand > 0): ?>
    <div class="card" id="dispose" style="max-width:600px">
        <div class="card-head"><h2>Dispose from this batch</h2><span class="sub">Currently <?php echo e($batch->qty_on_hand); ?> <?php echo e($batch->inventoryItem->unit_of_measure); ?> on hand</span></div>
        <form method="POST" action="<?php echo e(route('batches.dispose', $batch)); ?>" class="stack" onsubmit="return confirm('Record this disposal? This cannot be undone.');">
            <?php echo csrf_field(); ?>
            <div class="form-grid">
                <div class="field">
                    <label class="req">Quantity to dispose</label>
                    <input type="number" min="1" max="<?php echo e($batch->qty_on_hand); ?>" name="quantity" required>
                </div>
                <div class="field">
                    <label class="req">Reason</label>
                    <select name="reason" required>
                        <?php $__currentLoopData = ['Expired','Damaged','Contaminated','Packaging issue','Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r); ?>"><?php echo e($r); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field span-2">
                    <label>Remarks</label>
                    <input type="text" name="remarks">
                </div>
            </div>
            <div class="actions"><button type="submit" class="btn danger">Record disposal</button></div>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/batches/edit.blade.php ENDPATH**/ ?>