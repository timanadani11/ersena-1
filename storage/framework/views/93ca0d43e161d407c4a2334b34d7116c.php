<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido al SENA</title>
</head>
<body>
    <h1>¡Bienvenido <?php echo e($user->nombres_completos); ?>!</h1>
    <p>Tu registro ha sido exitoso. A continuación, encontrarás tu código QR para el registro de entradas y salidas:</p>
    
    <div>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo e($user->qr_code); ?>" alt="Tu código QR">
    </div>
    
    <p>Por favor, guarda este código QR ya que lo necesitarás para registrar tus entradas y salidas en el SENA.</p>
    
    <p>Tus credenciales de acceso son:</p>
    <ul>
        <li>Correo: <?php echo e($user->correo); ?></li>
        <li>Documento: <?php echo e($user->documento_identidad); ?></li>
    </ul>
    
    <p>Saludos,<br>
    Equipo SENA</p>
</body>
</html><?php /**PATH C:\laragon\www\ersena\resources\views\emails\welcome.blade.php ENDPATH**/ ?>