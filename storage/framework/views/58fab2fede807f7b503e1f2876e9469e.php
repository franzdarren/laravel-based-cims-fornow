<?php $__env->startSection('title', 'Suppliers'); ?>
<?php $__env->startSection('heading', 'Suppliers'); ?>
<?php $__env->startSection('subheading', 'Maintain supplier records'); ?>
<?php $__env->startSection('top-actions'); ?>
    <button type="button" class="btn primary" data-modal-open="new-supplier-template" data-modal-title="New supplier">+ New supplier</button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="toolbar">
        <div class="search-wrap">
            <input type="text" data-table-search="suppliersTable" placeholder="Search supplier name or contact">
        </div>
    </div>
    <div class="table-wrap">
        <table id="suppliersTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Supplier</th>
                <th data-sort="text">Contact person</th>
                <th data-sort="text">Contact no.</th>
                <th>Address</th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><b><?php echo e($s->name); ?></b><?php if($s->status === 'inactive'): ?><div class="muted small">Inactive</div><?php endif; ?></td>
                    <td><?php echo e($s->contact_person); ?></td>
                    <td class="mono"><?php echo e($s->contact_number); ?></td>
                    <td class="wrap"><?php echo e($s->address); ?></td>
                    <td>
                        <?php if($s->status === 'active'): ?>
                            <div class="actions">
                                <button type="button" class="btn small" data-modal-open="edit-supplier-<?php echo e($s->id); ?>" data-modal-title="Edit supplier">Edit</button>
                                <button type="button" class="btn small danger" data-modal-open="delete-supplier-<?php echo e($s->id); ?>" data-modal-title="Delete supplier">Delete</button>
                            </div>
                        <?php else: ?>
                            <span class="muted small">Soft deleted</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr class="empty-row"><td colspan="5" class="empty">No suppliers found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<template id="new-supplier-template">
    <form method="POST" action="<?php echo e(route('suppliers.store')); ?>" class="stack">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('suppliers._form', ['supplier' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="modal-actions">
            <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
            <button type="submit" class="btn primary">Save supplier</button>
        </div>
    </form>
</template>

<?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <template id="edit-supplier-<?php echo e($s->id); ?>">
        <form method="POST" action="<?php echo e(route('suppliers.update', $s)); ?>" class="stack">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <?php echo $__env->make('suppliers._form', ['supplier' => $s], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save supplier</button>
            </div>
        </form>
    </template>
    <template id="delete-supplier-<?php echo e($s->id); ?>">
        <div class="context-summary"><b><?php echo e($s->name); ?></b><?php echo e($s->contact_person ?: 'No contact person'); ?></div>
        <?php $blocked = $s->deletionBlockedMessage(); ?>
        <?php if($blocked): ?>
            <div class="notice danger"><?php echo e($blocked); ?></div>
            <div class="modal-actions"><button type="button" class="btn" onclick="CIMS.modal.close()">Close</button></div>
        <?php else: ?>
            <div class="notice warn">This is a soft delete. The supplier remains in historical transactions but will no longer be available for new receiving transactions.</div>
            <form method="POST" action="<?php echo e(route('suppliers.destroy', $s)); ?>" class="modal-actions">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn danger">Delete supplier</button>
            </form>
        <?php endif; ?>
    </template>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/suppliers/index.blade.php ENDPATH**/ ?>