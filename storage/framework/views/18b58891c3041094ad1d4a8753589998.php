

<?php $__env->startSection('title', 'Escáner QR - SENA Control de Asistencia'); ?>

<?php $__env->startSection('page-title', 'Escáner de QR'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .scanner-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-top: 1rem;
    }
    
    .scanner-info-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .qr-reader {
        width: 100%;
        height: 300px;
        border-radius: 8px;
        overflow: hidden;
        background-color: #000;
    }
    
    @media (max-width: 768px) {
        .scanner-container {
            grid-template-columns: 1fr;
        }
    }
    
    .scan-status {
        margin-top: 10px;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        background-color: #f0f0f0;
    }
    
    .scan-status.success {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }
    
    .scan-status.error {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    
    .scan-status.paused {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .fadeOut {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-qrcode"></i> Escaneo de Asistencia
        </div>
        <p class="text-muted">Utiliza el escáner para registrar las entradas y salidas de los aprendices.</p>
    </div>
    <div class="card-body">
        <?php echo $__env->make('admin.partials.scanner', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>

<?php echo $__env->make('admin.partials.audio', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\ersena\resources\views/admin/scanner/index.blade.php ENDPATH**/ ?>