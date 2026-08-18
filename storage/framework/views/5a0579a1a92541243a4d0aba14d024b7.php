<?php $__env->startSection('title', 'Receiving Records'); ?>
<?php $__env->startSection('heading', 'Receiving Records'); ?>
<?php $__env->startSection('subheading', 'Review all receiving transactions'); ?>
<?php $__env->startSection('top-actions'); ?>
    <a href="<?php echo e(route('receiving.create')); ?>" class="btn primary">New receiving transaction</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="stack">
    <div class="card">
        <div class="card-head"><h2>Pending requests</h2><span class="badge amber"><?php echo e($myPending->count()); ?> pending</span></div>
        <div class="table-wrap">
            <table id="pendingReceivingTable" data-enhance>
                <thead><tr><th data-sort="text">Reference</th><th data-sort="date">Date</th><th>Supplier</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $myPending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="mono"><?php echo e($r->reference_no); ?></td>
                        <td class="mono nowrap" data-sort-value="<?php echo e($r->date_received); ?>"><?php echo e($r->date_received->format('d M Y')); ?></td>
                        <td><?php echo e($r->supplier->name); ?></td>
                        <td><span class="badge amber">Pending</span></td>
                        <td><button type="button" class="btn small danger" data-modal-open="cancel-receiving-<?php echo e($r->id); ?>" data-modal-title="Cancel pending request">Cancel request</button></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr class="empty-row"><td colspan="5" class="empty">You have no pending requests.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Returned requests</h2><span class="badge amber"><?php echo e($myReturned->count()); ?> returned</span></div>
        <div class="card-body stack">
        <?php $__empty_1 = true; $__currentLoopData = $myReturned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card">
                <div class="card-head">
                    <h3 class="mono"><?php echo e($r->reference_no); ?></h3>
                    <div class="actions">
                        <button type="button" class="btn small" data-modal-open="edit-returned-details-<?php echo e($r->id); ?>" data-modal-title="Edit receiving details">Edit transaction details</button>
                        <form method="POST" action="<?php echo e(route('receiving.returned.resubmit', $r)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn small primary">Resubmit for approval</button>
                        </form>
                    </div>
                </div>
                <div class="grid cols-4" style="margin-bottom:10px">
                    <div><b>Supplier</b><div><?php echo e($r->supplier->name); ?></div></div>
                    <div><b>Date received</b><div class="mono"><?php echo e($r->date_received->format('d M Y')); ?></div></div>
                    <div><b>Remarks</b><div><?php echo e($r->remarks ?: '—'); ?></div></div>
                    <div><b>Return reason</b><div><?php echo e($r->return_reason ?: '—'); ?></div></div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Item</th><th>Quantity</th><th>Details</th><th></th></tr></thead>
                        <tbody>
                        <?php $__currentLoopData = $r->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $i = $l->inventoryItem; $eq = $i->category === 'equipment'; ?>
                            <tr>
                                <td><b><?php echo e($i->name); ?></b><div class="muted small"><?php echo e($i->item_code); ?></div></td>
                                <td class="mono"><?php echo e($l->quantity); ?></td>
                                <td class="mono small">
                                    <?php if($eq): ?>
                                        Brand: <?php echo e($l->brand ?: '—'); ?> · Model: <?php echo e($l->model ?: '—'); ?> · Serial: <?php echo e($l->serial_number ?: '—'); ?> · Asset tag: <?php echo e($l->asset_tag ?: '—'); ?> · Location: <?php echo e($l->location ?: '—'); ?>

                                    <?php else: ?>
                                        Brand: <?php echo e($l->brand ?: '—'); ?> · Batch: <?php echo e($l->batch_number ?: '—'); ?> · Expiry: <?php echo e($l->expiry_date?->format('d M Y') ?: '—'); ?> · Location: <?php echo e($l->location ?: '—'); ?>

                                    <?php endif; ?>
                                </td>
                                <td><button type="button" class="btn small" data-modal-open="edit-returned-line-<?php echo e($l->id); ?>" data-modal-title="Edit returned item">Edit item</button></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty">You have no returned requests.</div>
        <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Recent receiving records</h2></div>
        <div class="toolbar"><div class="search-wrap"><input type="text" data-table-search="recentReceivingTable" placeholder="Search recent receiving records"></div></div>
        <div class="table-wrap">
            <table id="recentReceivingTable" data-enhance>
                <thead><tr><th data-sort="text">Reference</th><th>Supplier</th><th data-sort="date">Date</th><th data-sort="number" class="right">Items</th><th data-sort="text">Status</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $tone = match($r->status){'APPROVED'=>'green','CANCELLED'=>'red',default=>''}; ?>
                    <tr>
                        <td class="mono"><?php echo e($r->reference_no); ?></td>
                        <td><?php echo e($r->supplier->name); ?></td>
                        <td class="mono nowrap" data-sort-value="<?php echo e($r->date_received); ?>"><?php echo e($r->date_received->format('d M Y')); ?></td>
                        <td class="right mono"><?php echo e($r->lines()->count()); ?></td>
                        <td><span class="badge <?php echo e($tone); ?>"><?php echo e(ucfirst(strtolower($r->status))); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr class="empty-row"><td colspan="5" class="empty">No completed receiving records yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $__currentLoopData = $myPending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <template id="cancel-receiving-<?php echo e($r->id); ?>">
        <div class="notice warn"><b>Receiving request <?php echo e($r->reference_no); ?></b><br>This removes the request from the Supervisor approval queue. The cancellation remains in the audit log.</div>
        <form method="POST" action="<?php echo e(route('receiving.cancel', $r)); ?>" class="stack" style="margin-top:12px">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label class="req">Cancellation reason</label>
                <textarea name="reason" placeholder="Enter the reason for cancelling this request" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Keep request</button>
                <button type="submit" class="btn danger">Cancel pending request</button>
            </div>
        </form>
    </template>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__currentLoopData = $myReturned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <template id="edit-returned-details-<?php echo e($r->id); ?>">
        <form method="POST" action="<?php echo e(route('receiving.returned.details', $r)); ?>" class="stack">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="form-grid">
                <div class="field span-2">
                    <label class="req">Supplier</label>
                    <select name="supplier_id" required>
                        <?php $__currentLoopData = \App\Models\Supplier::where('status', 'active')->orWhere('id', $r->supplier_id)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->id); ?>" <?php if($s->id === $r->supplier_id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field">
                    <label class="req">Reference number</label>
                    <input type="text" class="mono" name="reference_no" value="<?php echo e($r->reference_no); ?>" required>
                </div>
                <div class="field">
                    <label class="req">Date received</label>
                    <input type="date" name="date_received" value="<?php echo e($r->date_received->format('Y-m-d')); ?>" required>
                </div>
                <div class="field span-4">
                    <label>Remarks</label>
                    <textarea name="remarks" maxlength="150"><?php echo e($r->remarks); ?></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save details</button>
            </div>
        </form>
    </template>
    <?php $__currentLoopData = $r->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $i = $l->inventoryItem; $eq = $i->category === 'equipment'; ?>
        <template id="edit-returned-line-<?php echo e($l->id); ?>">
            <form method="POST" action="<?php echo e(route('receiving.returned.line', [$r, $l])); ?>" class="stack">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="context-summary"><b><?php echo e($i->name); ?></b> <span class="mono"><?php echo e($i->item_code); ?></span></div>
                <div class="form-grid">
                    <div class="field">
                        <label class="req">Quantity</label>
                        <input type="number" min="1" <?php if($eq): ?> max="1" <?php endif; ?> name="quantity" value="<?php echo e($l->quantity); ?>" required>
                    </div>
                    <div class="field">
                        <label>Brand</label>
                        <input type="text" name="brand" value="<?php echo e($l->brand); ?>">
                    </div>
                    <?php if($eq): ?>
                        <div class="field">
                            <label>Equipment model</label>
                            <input type="text" name="model" value="<?php echo e($l->model); ?>">
                        </div>
                        <div class="field">
                            <label class="req">Serial number</label>
                            <input type="text" class="mono" name="serial_number" value="<?php echo e($l->serial_number); ?>" required>
                        </div>
                        <div class="field">
                            <label class="req">Asset tag</label>
                            <input type="text" class="mono" name="asset_tag" value="<?php echo e($l->asset_tag); ?>" required>
                        </div>
                    <?php else: ?>
                        <div class="field">
                            <label>Batch number <span class="muted">(optional)</span></label>
                            <input type="text" class="mono" name="batch_number" value="<?php echo e($l->batch_number); ?>">
                        </div>
                        <div class="field">
                            <label>Expiry date</label>
                            <input type="date" name="expiry_date" value="<?php echo e($l->expiry_date?->format('Y-m-d')); ?>">
                        </div>
                    <?php endif; ?>
                    <div class="field">
                        <label class="req">Location</label>
                        <select name="location" required>
                            <option value="">Select location</option>
                            <?php $__currentLoopData = \App\Http\Controllers\SettingController::locationList(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($loc); ?>" <?php if($l->location === $loc): echo 'selected'; endif; ?>><?php echo e($loc); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                    <button type="submit" class="btn primary">Save item</button>
                </div>
            </form>
        </template>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/receiving/index.blade.php ENDPATH**/ ?>