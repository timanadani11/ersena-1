

<?php $__env->startSection('title', 'Registro de Asistencias - SENA Control de Asistencia'); ?>

<?php $__env->startSection('page-title', 'Registro de Asistencias'); ?>

<?php $__env->startSection('content'); ?>
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-calendar-check"></i> Registro de Asistencias
        </div>
        <div class="card-actions">
            <div class="search-form">
                <form method="GET" action="<?php echo e(route('admin.asistencias.index')); ?>" class="search-form-inner">
                    <input type="text" name="search" placeholder="Buscar por nombre o documento..." class="form-control form-control-sm" value="<?php echo e(request()->search); ?>">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="export-buttons">
                <a href="#" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="#" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="filter-section">
            <form method="GET" action="<?php echo e(route('admin.asistencias.index')); ?>" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-item">
                        <label>Fecha inicio:</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?php echo e(request()->fecha_inicio); ?>">
                    </div>
                    <div class="filter-item">
                        <label>Fecha fin:</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?php echo e(request()->fecha_fin); ?>">
                    </div>
                    <div class="filter-item">
                        <label>Programa:</label>
                        <select name="programa_id" class="form-control form-control-sm">
                            <option value="">Todos los programas</option>
                            <!-- <?php $__currentLoopData = $programas ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $programa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($programa->id); ?>" <?php echo e(request()->programa_id == $programa->id ? 'selected' : ''); ?>>
                                    <?php echo e($programa->nombre_programa); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> -->
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Tipo:</label>
                        <select name="tipo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="entrada" <?php echo e(request()->tipo == 'entrada' ? 'selected' : ''); ?>>Entrada</option>
                            <option value="salida" <?php echo e(request()->tipo == 'salida' ? 'selected' : ''); ?>>Salida</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                        <a href="<?php echo e(route('admin.asistencias.index')); ?>" class="btn btn-sm btn-secondary">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Documento</th>
                        <th>Aprendiz</th>
                        <th>Programa</th>
                        <th>Ficha</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $asistencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asistencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($asistencia->fecha_hora->format('d/m/Y H:i')); ?></td>
                        <td><?php echo e($asistencia->user->documento_identidad); ?></td>
                        <td><?php echo e($asistencia->user->nombres_completos); ?></td>
                        <td><?php echo e($asistencia->user->programaFormacion->nombre_programa ?? 'N/A'); ?></td>
                        <td><?php echo e($asistencia->user->programaFormacion->numero_ficha ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge <?php echo e($asistencia->tipo === 'entrada' ? 'bg-success' : 'bg-info'); ?>">
                                <?php echo e($asistencia->tipo === 'entrada' ? 'Entrada' : 'Salida'); ?>

                            </span>
                        </td>
                        <td>
                            <?php
                                $estado = 'A tiempo';
                                $badgeClass = 'bg-success';
                                
                                if ($asistencia->tipo === 'entrada' && $asistencia->es_tarde) {
                                    $estado = 'Tarde';
                                    $badgeClass = 'bg-danger';
                                }
                            ?>
                            <span class="badge <?php echo e($badgeClass); ?>"><?php echo e($estado); ?></span>
                        </td>
                        <td><?php echo e($asistencia->registradoPor->nombres_completos ?? 'Sistema'); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center">No hay registros de asistencia</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            <?php echo e($asistencias->appends(request()->query())->links()); ?>

        </div>
    </div>
</div>

<style>
.card-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.search-form {
    flex: 1;
}

.search-form-inner {
    display: flex;
    gap: 0.5rem;
}

.export-buttons {
    display: flex;
    gap: 0.5rem;
}

.filter-section {
    margin-bottom: 1.5rem;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-item label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: flex-end;
}

.badge {
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    font-weight: 600;
    color: #fff;
    border-radius: 4px;
}

.bg-success {
    background-color: #10b981;
}

.bg-danger {
    background-color: #ef4444;
}

.bg-info {
    background-color: #3b82f6;
}
</style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\ersena\resources\views/admin/asistencias/index.blade.php ENDPATH**/ ?>