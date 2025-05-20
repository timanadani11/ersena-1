<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title><?php echo e(config('app.name', 'ERSENA')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/common.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.css')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldContent('styles'); ?>
    
    <script>
        // Prevenir navegación con botones del navegador
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };

        // Verificar sesión en cada cambio de ruta
        document.addEventListener('DOMContentLoaded', function() {
            checkSession();
        });

        function checkSession() {
            fetch('/check-session', {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.authenticated) {
                    window.location.href = '<?php echo e(route("login")); ?>';
                }
            })
            .catch(error => {
                console.error('Error verificando sesión:', error);
                window.location.href = '<?php echo e(route("login")); ?>';
            });
        }

        // Verificar sesión cada 1 minuto
        <?php if(auth()->guard()->check()): ?>
            setInterval(checkSession, 60000);
        <?php endif; ?>

        // Prevenir el uso del botón atrás después del logout
        window.onload = function() {
            if (window.history && window.history.pushState) {
                window.history.pushState('forward', null, window.location.href);
                window.onpopstate = function() {
                    window.history.pushState('forward', null, window.location.href);
                    checkSession();
                };
            }
        }

        // Detectar cuando la pestaña/ventana se vuelve activa
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                checkSession();
            }
        });
    </script>
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="<?php echo e(url('/')); ?>">
                <img src="<?php echo e(asset('img/logo/logo.png')); ?>" alt="ERSENA Logo">
            </a>
        </div>
        <nav class="nav-links">
            <?php if(auth()->guard()->guest()): ?>
                <a href="<?php echo e(route('login')); ?>" class="login-btn">Iniciar Sesión</a>
                <a href="<?php echo e(route('register')); ?>" class="register-btn">Registrarse</a>
            <?php else: ?>
                <div class="nav-menu">
                    <a href="<?php echo e(route('home')); ?>" class="nav-link">Inicio</a>
                    <?php if(auth()->user()->hasRole('admin')): ?>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">Panel Admin</a>
                    <?php endif; ?>
                    <?php if(auth()->user()->hasRole('aprendiz')): ?>
                        <a href="<?php echo e(route('aprendiz.dashboard')); ?>" class="nav-link">Mi Panel</a>
                    <?php endif; ?>
                    <div class="user-menu">
                        <span><?php echo e(Auth::user()->name); ?></span>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="nav-link logout-btn" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?')">
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo e(date('Y')); ?> ERSENA. Todos los derechos reservados.</p>
        </div>
    </footer>

    <?php echo $__env->yieldContent('scripts'); ?>
    
    <script>
        // Cerrar sesión automáticamente después de 30 minutos de inactividad
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeydown = resetTimer;

            function logout() {
                document.getElementById('logout-form').submit();
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(logout, 1800000); // 30 minutos
            }
        };
        <?php if(auth()->guard()->check()): ?>
            inactivityTime();
        <?php endif; ?>
    </script>
</body>
</html><?php /**PATH C:\laragon\www\ersena\resources\views\layouts\app.blade.php ENDPATH**/ ?>