<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#39A900">
    <title>Login</title>    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo e(asset('css/common.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&family=Poppins&display=swap">
    <!-- icon -->
    <link rel="icon" href="<?php echo e(asset('img/icon/icono.ico')); ?>" type="image/x-icon">
</head> 
<body class="index-page">

    <header class="index-header">
        <div class="logo">
            <img src="<?php echo e(asset('img/logo/logo.webp')); ?>" alt="logo">
        </div>
    </header>

    <main class="index-login-container">
        <div class="index-login-card">
            <h2 class="index-title">Iniciar Sesión</h2>

            
            <?php if($errors->any()): ?>
                <div class="index-alert index-alert-danger">
                    <ul style="margin: 0;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>">
                <?php echo csrf_field(); ?>

                <div class="index-form-group">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" required>
                </div>

                <div class="index-form-group index-password-container">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="index-btn-login">Iniciar Sesión</button>
            </form>

            <div class="index-registro-link">
                <p>¿No tienes una cuenta? <a href="<?php echo e(route('register')); ?>">Registrate</a></p>
            </div>
        </div>
    </main>

</body>
</html>
<?php /**PATH C:\laragon\www\ersena\resources\views\auth\login.blade.php ENDPATH**/ ?>