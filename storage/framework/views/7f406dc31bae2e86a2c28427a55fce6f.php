

<?php $__env->startSection('title', 'Gestión de Programas - SENA Control de Asistencia'); ?>

<?php $__env->startSection('page-title', 'Gestión de Programas'); ?>

<?php $__env->startSection('content'); ?>
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-book"></i> Programas de Formación
        </div>
        <div class="card-actions">
            <a href="#" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nuevo Programa
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre del Programa</th>
                        <th>Ficha</th>
                        <th>Nivel</th>
                        <th>Instructor</th>
                        <th>Aprendices</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $programas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $programa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($programa->nombre_programa); ?></td>
                        <td><?php echo e($programa->numero_ficha); ?></td>
                        <td><?php echo e($programa->nivel_formacion); ?></td>
                        <td><?php echo e($programa->user ? $programa->user->nombres_completos : 'Sin asignar'); ?></td>
                        <td><?php echo e($programa->aprendices_count ?? 0); ?></td>
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
                        <td colspan="6" class="text-center">No hay programas registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            <?php echo e($programas->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\ersena\resources\views/admin/programas/index.blade.php ENDPATH**/ ?>