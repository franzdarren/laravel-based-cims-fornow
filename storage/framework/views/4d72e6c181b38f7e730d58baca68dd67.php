<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · CIMS</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="brand">
            <div class="brand-mark">✚</div>
            <div><b>Clinic Inventory</b><span>Management System</span></div>
        </div>

        <?php if($errors->any()): ?>
            <div class="notice danger" style="margin-bottom:14px">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login.attempt')); ?>" class="stack">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label class="req">Email</label>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" autofocus required>
            </div>
            <div class="field">
                <label class="req">Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn primary" style="justify-content:center">Sign in</button>
        </form>

        <div class="login-hint">
            <b>Demo accounts</b> (seeded sample data):<br>
            Administrator — <span class="mono">avillanueva@clinic.local</span> / <span class="mono">password</span><br>
            Nurse — <span class="mono">ncruz@clinic.local</span> / <span class="mono">password</span><br>
            Supervisor — <span class="mono">mlim@clinic.local</span> / <span class="mono">password</span>
        </div>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\cims\resources\views/auth/login.blade.php ENDPATH**/ ?>