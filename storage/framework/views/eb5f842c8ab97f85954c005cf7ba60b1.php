<?php $__env->startSection('title', 'New Issuance'); ?>
<?php $__env->startSection('heading', 'New issuance'); ?>
<?php $__env->startSection('subheading', 'Add each item, then record the issuance'); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="card">
        <div class="card-head"><h2>Visit details</h2></div>
        <form method="POST" action="<?php echo e(route('issuance.lines.add')); ?>" class="stack">
            <?php echo csrf_field(); ?>
            <div class="form-grid">
                <div class="field">
                    <label class="req">Employee number</label>
                    <input type="text" name="employee_no" value="<?php echo e($header['employee_no'] ?? ''); ?>" required>
                </div>
                <div class="field">
                    <label>Department</label>
                    <input type="text" name="department" value="<?php echo e($header['department'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Employee's supervisor</label>
                    <input type="text" name="employee_supervisor" value="<?php echo e($header['employee_supervisor'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label class="req">Disposition</label>
                    <select name="disposition" required>
                        <?php $__currentLoopData = ['Returned to work','Sent home','Referred to hospital']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($d); ?>" <?php if(($header['disposition'] ?? '') === $d): echo 'selected'; endif; ?>><?php echo e($d); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field span-2">
                    <label class="req">Chief complaint</label>
                    <input type="text" name="chief_complaint" value="<?php echo e($header['chief_complaint'] ?? ''); ?>" required>
                </div>
                <div class="field span-2">
                    <label>Remarks</label>
                    <input type="text" name="remarks" value="<?php echo e($header['remarks'] ?? ''); ?>">
                </div>
            </div>

            <hr>

            <div class="form-grid">
                <div class="field">
                    <label class="req">Item</label>
                    <select name="item_id" required>
                        <option value="">— Select —</option>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?> (<?php echo e($i->stockOnHand()); ?> <?php echo e($i->unit_of_measure); ?> on hand)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field">
                    <label class="req">Quantity</label>
                    <input type="number" min="1" name="quantity" required>
                </div>
            </div>
            <div class="actions"><button type="submit" class="btn primary">Add item to issuance</button></div>
        </form>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Items added so far</h2>
            <?php if(count($lines)): ?>
                <form method="POST" action="<?php echo e(route('issuance.draft.clear')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn small">Clear all items</button>
                </form>
            <?php endif; ?>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="allocation-box" style="margin-bottom:10px">
                <div class="allocation-title"><?php echo e($l['item_name']); ?> — <?php echo e($l['quantity']); ?> <?php echo e($l['unit_of_measure']); ?></div>
                <div class="small">FEFO quantity allocation:
                    <?php $__currentLoopData = $l['allocation_preview']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="mono"><?php echo e($a['batch_number']); ?></span> (exp <?php echo e($a['expiry_date'] ?? 'n/a'); ?>) × <?php echo e($a['qty']); ?><?php if(!$loop->last): ?>, <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <form method="POST" action="<?php echo e(route('issuance.lines.remove', $idx)); ?>" style="margin-top:6px">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn small danger">Remove</button>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="muted">No items added yet.</p>
        <?php endif; ?>

        <?php if(count($lines)): ?>
        <form method="POST" action="<?php echo e(route('issuance.store')); ?>" style="margin-top:10px">
            <?php echo csrf_field(); ?>
            <div class="actions">
                <button type="submit" class="btn primary">Record issuance</button>
                <a href="<?php echo e(route('issuance.index')); ?>" class="btn">Back to Issuance</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/issuance/create.blade.php ENDPATH**/ ?>