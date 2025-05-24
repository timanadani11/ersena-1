

<?php $__env->startSection('title', 'Gestión de Aprendices - SENA Control de Asistencia'); ?>

<?php $__env->startSection('page-title', 'Gestión de Aprendices'); ?>

<?php $__env->startSection('content'); ?>
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-users"></i> Listado de Aprendices
        </div>
        <div class="card-actions">
            <a href="#" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nuevo Aprendiz
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre Completo</th>
                        <th>Programa</th>
                        <th>Ficha</th>
                        <th>Jornada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $aprendices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aprendiz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($aprendiz->documento_identidad); ?></td>
                        <td><?php echo e($aprendiz->nombres_completos); ?></td>
                        <td><?php echo e($aprendiz->programaFormacion ? $aprendiz->programaFormacion->nombre_programa : 'N/A'); ?></td>
                        <td><?php echo e($aprendiz->programaFormacion ? $aprendiz->programaFormacion->numero_ficha : 'N/A'); ?></td>
                        <td><?php echo e($aprendiz->jornada ? $aprendiz->jornada->nombre : 'N/A'); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="#" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center">No hay aprendices registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            <?php echo e($aprendices->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\ersena\resources\views/admin/aprendices/index.blade.php ENDPATH**/ ?>