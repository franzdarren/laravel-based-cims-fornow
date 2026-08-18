<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> · CIMS</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
</head>
<body>
<?php
    $role = auth()->user()->role->role_name ?? null;
    $navItem = function (string $routeName, string $label, string $icon) {
        $active = request()->routeIs($routeName.'*');
        return '<a href="'.route($routeName).'" class="'.($active ? 'active' : '').'"><span class="icon">'.$icon.'</span>'.$label.'</a>';
    };
?>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">✚</div>
            <div><b>Clinic Inventory</b><span>Management System</span></div>
        </div>

        <div class="role-card">
            <label>Signed in as</label>
            <div class="name"><?php echo e(auth()->user()->fullname); ?></div>
            <div class="muted small"><?php echo e($role); ?></div>
        </div>

        <nav class="nav">
            <?php echo $navItem('dashboard', 'Dashboard', '▦'); ?>


            <?php if($role === 'Nurse'): ?>
                <?php echo $navItem('receiving.create', 'Receiving', '📥'); ?>

                <?php echo $navItem('receiving.index', 'Receiving Records', '📄'); ?>

                <?php echo $navItem('issuance.index', 'Issuance', '📤'); ?>

                <?php echo $navItem('batches.index', 'Batches', '🧴'); ?>

                <?php echo $navItem('equipment.index', 'Equipment', '🩺'); ?>

                <?php echo $navItem('disposals.index', 'Disposals', '🗑'); ?>

                <?php echo $navItem('suppliers.index', 'Suppliers', '🏭'); ?>

            <?php endif; ?>

            <?php if($role === 'Supervisor'): ?>
                <?php echo $navItem('approvals.index', 'Approvals', '✅'); ?>

                <?php echo $navItem('receiving.records', 'Receiving Records', '📄'); ?>

                <?php echo $navItem('batches.index', 'Batches', '🧴'); ?>

                <?php echo $navItem('equipment.index', 'Equipment', '🩺'); ?>

                <?php echo $navItem('disposals.index', 'Disposals', '🗑'); ?>

                <?php echo $navItem('reports.index', 'Reports', '📊'); ?>

            <?php endif; ?>

            <?php if($role === 'Administrator'): ?>
                <?php echo $navItem('items.index', 'Item Master', '📚'); ?>

                <?php echo $navItem('users.index', 'Users', '👤'); ?>

                <?php echo $navItem('roles.index', 'Roles', '🔐'); ?>

                <?php echo $navItem('settings.edit', 'System Settings', '⚙'); ?>

                <?php echo $navItem('suppliers.index', 'Suppliers', '🏭'); ?>

            <?php endif; ?>

            <?php echo $navItem('logs.index', 'Transaction Log', '🧾'); ?>

        </nav>

        <div class="sidebar-foot">
            <span>CIMS · Laravel + MySQL</span>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit">⎋ Log out</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="page-heading">
                <h1><?php echo $__env->yieldContent('heading', 'Dashboard'); ?></h1>
                <p><?php echo $__env->yieldContent('subheading', ''); ?></p>
            </div>
            <div class="top-actions">
                <?php echo $__env->yieldContent('top-actions'); ?>
            </div>
        </header>

        <div class="content">
            <?php if($errors->any()): ?>
                <div class="notice danger" style="margin-bottom:16px">
                    <b>Please check the form below:</b>
                    <ul style="margin:6px 0 0;padding-left:18px">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>
</div>

<div class="modal-back" id="modalBack" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modalTitle"></h3>
            <button type="button" class="btn small" id="modalClose">Close</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>
<div class="toast" id="toast"></div>

<?php if(session('status')): ?>
    <script>window.CIMS_STATUS = <?php echo json_encode(session('status'), 15, 512) ?>;</script>
<?php endif; ?>
<script src="<?php echo e(asset('js/app.js')); ?>"></script>
<?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\cims\resources\views/layouts/app.blade.php ENDPATH**/ ?>