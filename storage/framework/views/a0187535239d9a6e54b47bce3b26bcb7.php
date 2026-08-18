<?php $__env->startSection('title', 'Equipment'); ?>
<?php $__env->startSection('heading', 'Equipment'); ?>
<?php $__env->startSection('subheading', 'Track individual equipment units and open disposal dialogs'); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="toolbar">
        <div class="search-wrap">
            <input type="text" data-table-search="equipmentTable" placeholder="Search equipment, asset tag, serial, or location">
        </div>
    </div>
    <div class="card">
        <div class="card-head"><h2>Equipment unit records</h2><span class="sub">One record per physical unit</span></div>
        <div class="table-wrap">
            <table id="equipmentTable" data-enhance>
                <thead><tr>
                    <th data-sort="text">Equipment</th>
                    <th data-sort="text">Asset tag</th>
                    <th>Serial no.</th>
                    <th>Brand / model</th>
                    <th data-sort="text">Location</th>
                    <th data-sort="date">Acquired</th>
                    <th data-sort="text">Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $equipment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><b><?php echo e($e->inventoryItem->name); ?></b><div class="muted small mono"><?php echo e($e->inventoryItem->item_code); ?></div></td>
                        <td class="mono"><?php echo e($e->asset_tag); ?></td>
                        <td class="mono"><?php echo e($e->serial_number ?: '—'); ?></td>
                        <td><?php echo e(trim(($e->brand ?? '').' '.($e->model ?? '')) ?: '—'); ?></td>
                        <td><?php echo e($e->location); ?></td>
                        <td class="mono nowrap" data-sort-value="<?php echo e($e->acquired_date); ?>"><?php echo e($e->acquired_date?->format('d M Y') ?? '—'); ?></td>
                        <td>
                            <?php $tone = match($e->status){'in_use'=>'green','maintenance'=>'amber','disposed'=>'red','retired'=>'red',default=>''}; ?>
                            <span class="badge <?php echo e($tone); ?>"><?php echo e(ucfirst(str_replace('_',' ',$e->status))); ?></span>
                        </td>
                        <td>
                            <?php if(auth()->user()->isRole('Nurse')): ?>
                                <?php if($e->status !== 'disposed'): ?>
                                    <div class="actions">
                                        <button type="button" class="btn small" data-modal-open="edit-equipment-<?php echo e($e->id); ?>" data-modal-title="Edit <?php echo e($e->asset_tag); ?>">Edit</button>
                                        <button type="button" class="btn small danger" data-modal-open="dispose-equipment-<?php echo e($e->id); ?>" data-modal-title="Record equipment disposal">Dispose</button>
                                    </div>
                                <?php else: ?>
                                    <span class="muted small">Disposed record</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="muted small">View only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr class="empty-row"><td colspan="8" class="empty">No equipment records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(auth()->user()->isRole('Nurse')): ?>
<?php $__currentLoopData = $equipment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($e->status !== 'disposed'): ?>
        <template id="edit-equipment-<?php echo e($e->id); ?>">
            <div class="context-summary"><b><?php echo e($e->inventoryItem->name); ?></b><span class="mono"><?php echo e($e->asset_tag); ?></span> · Serial: <?php echo e($e->serial_number ?: '—'); ?> · <?php echo e($e->brand); ?> <?php echo e($e->model); ?> · <?php echo e($e->location ?: '—'); ?></div>
            <form method="POST" action="<?php echo e(route('equipment.update', $e)); ?>" class="stack">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="form-grid">
                    <div class="field"><label>Brand</label><input type="text" name="brand" value="<?php echo e($e->brand); ?>"></div>
                    <div class="field"><label>Model</label><input type="text" name="model" value="<?php echo e($e->model); ?>"></div>
                    <div class="field span-2"><label>Location</label><input type="text" name="location" value="<?php echo e($e->location); ?>"></div>
                    <div class="field span-2">
                        <label class="req">Status</label>
                        <select name="status" required>
                            <?php $__currentLoopData = ['in_use'=>'in_use','maintenance'=>'maintenance','retired'=>'retired']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>" <?php if($e->status === $val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="field span-2"><label class="req">Adjustment reason</label><input type="text" name="reason" required></div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                    <button type="submit" class="btn primary">Save equipment status</button>
                </div>
            </form>
        </template>
        <template id="dispose-equipment-<?php echo e($e->id); ?>">
            <div class="context-summary"><b><?php echo e($e->inventoryItem->name); ?></b><span class="mono"><?php echo e($e->asset_tag); ?></span> · <?php echo e($e->brand); ?> <?php echo e($e->model); ?></div>
            <form method="POST" action="<?php echo e(route('equipment.dispose', $e)); ?>" class="stack">
                <?php echo csrf_field(); ?>
                <div class="field">
                    <label class="req">Disposal reason</label>
                    <select name="reason" required>
                        <?php $__currentLoopData = ['Beyond useful life','Damaged beyond repair','Unsafe for use','Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r); ?>"><?php echo e($r); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field" style="margin-top:11px"><label>Remarks</label><textarea name="remarks"></textarea></div>
                <div class="notice warn" style="margin-top:12px">Equipment is tracked per unit, so the disposal quantity is fixed at 1.</div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                    <button type="submit" class="btn danger">Record disposal</button>
                </div>
            </form>
        </template>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/equipment/index.blade.php ENDPATH**/ ?>