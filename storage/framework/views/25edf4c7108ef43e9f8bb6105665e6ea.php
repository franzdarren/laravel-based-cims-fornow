<?php $__env->startSection('title', 'Receiving Records'); ?>
<?php $__env->startSection('heading', 'Receiving Records'); ?>
<?php $__env->startSection('subheading', 'Review all receiving transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-head">
        <h2>Receiving transaction records</h2>
        <div class="search-wrap"><input type="text" data-table-search="receivingRecordsTable" placeholder="Search reference, supplier, or status"></div>
    </div>
    <div class="table-wrap">
        <table id="receivingRecordsTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Reference</th>
                <th data-sort="date">Date</th>
                <th data-sort="text">Supplier</th>
                <th>Encoded by</th>
                <th>Approved by</th>
                <th data-sort="text">Status</th>
            </tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $tone = match($r->status){'APPROVED'=>'green','PENDING'=>'amber','RETURNED'=>'red','CANCELLED'=>'red',default=>''}; ?>
                <tr>
                    <td class="mono"><?php echo e($r->ref_no); ?></td>
                    <td class="mono nowrap" data-sort-value="<?php echo e($r->date_received); ?>"><?php echo e(optional($r->date_received)->format('d M Y')); ?></td>
                    <td><?php echo e($r->supplier->supplier_name); ?></td>
                    <td><?php echo e($r->receivedBy->fullname ?? '—'); ?></td>
                    <td><?php echo e($r->approvedBy->fullname ?? '—'); ?></td>
                    <td><span class="badge <?php echo e($tone); ?>"><?php echo e(ucfirst(strtolower($r->status))); ?></span></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr class="empty-row"><td colspan="6" class="empty">No receiving records yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cims\resources\views/receiving/records.blade.php ENDPATH**/ ?>