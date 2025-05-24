<!-- Audio elements -->
<audio id="sound-entrada" src="<?php echo e(asset('sounds/entrada.mp3')); ?>" preload="auto"></audio>
<audio id="sound-salida" src="<?php echo e(asset('sounds/salida.mp3')); ?>" preload="auto"></audio>
<audio id="sound-error" src="<?php echo e(asset('sounds/error.mp3')); ?>" preload="auto"></audio>
<audio id="sound-scan" src="<?php echo e(asset('sounds/scan.mp3')); ?>" preload="auto"></audio>

<script>
// Reproducir sonido según el caso
function reproducirSonido(tipo) {
    let audio;
    
    switch(tipo) {
        case 'entrada':
            audio = document.getElementById('sound-entrada');
            break;
        case 'salida':
            audio = document.getElementById('sound-salida');
            break;
        case 'error':
            audio = document.getElementById('sound-error');
            break;
        case 'scan':
            audio = document.getElementById('sound-scan');
            break;
        default:
            return;
    }
    
    // Asegurarse de reiniciar el audio antes de reproducirlo
    audio.pause();
    audio.currentTime = 0;
    audio.play().catch(e => console.log('No se pudo reproducir el sonido'));
}
</script> <?php /**PATH C:\laragon\www\ersena\resources\views/admin/partials/audio.blade.php ENDPATH**/ ?>