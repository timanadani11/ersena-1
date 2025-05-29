@extends('layouts.admin')

@section('title', 'Configuración - SENA Control de Asistencia')

@section('page-title', 'Configuración del Sistema')

@section('content')
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-cog"></i> Configuración General
        </div>
        <p class="text-muted">Configure los parámetros generales del sistema de asistencia.</p>
    </div>
    <div class="card-body">
        <div class="config-tabs">
            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                        <i class="fas fa-sliders-h"></i> General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="jornadas-tab" data-toggle="tab" href="#jornadas" role="tab">
                        <i class="fas fa-clock"></i> Jornadas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="usuarios-tab" data-toggle="tab" href="#usuarios" role="tab">
                        <i class="fas fa-user-shield"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sistema-tab" data-toggle="tab" href="#sistema" role="tab">
                        <i class="fas fa-server"></i> Sistema
                    </a>
                </li>
            </ul>
            
            <div class="tab-content mt-3" id="configTabsContent">
                <!-- Configuración General -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <form id="form-config-general">
                        <div class="form-group">
                            <label for="nombre_institucion">Nombre de la Institución</label>
                            <input type="text" class="form-control" id="nombre_institucion" value="Servicio Nacional de Aprendizaje - SENA">
                        </div>
                        <div class="form-group">
                            <label for="sede">Sede</label>
                            <input type="text" class="form-control" id="sede" value="Centro de Servicios y Gestión Empresarial">
                        </div>
                        <div class="form-group">
                            <label for="tolerancia_global">Tolerancia Global (minutos)</label>
                            <input type="number" class="form-control" id="tolerancia_global" value="15">
                            <small class="form-text text-muted">Tiempo de tolerancia para las llegadas tarde (en minutos)</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
                
                <!-- Configuración de Jornadas -->
                <div class="tab-pane fade" id="jornadas" role="tabpanel">
                    <div class="jornadas-list">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Hora Entrada</th>
                                        <th>Hora Salida</th>
                                        <th>Tolerancia (min)</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Las jornadas se cargarán aquí -->
                                </tbody>
                            </table>
                        </div>
                        <button id="btn-nueva-jornada" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nueva Jornada
                        </button>
                    </div>
                </div>
                
                <!-- Configuración de Usuarios -->
                <div class="tab-pane fade" id="usuarios" role="tabpanel">
                    <p class="text-muted">Gestione los usuarios administrativos del sistema.</p>
                    <div class="usuarios-admin-list">
                        <!-- Lista de usuarios administrativos -->
                    </div>
                    <button id="btn-nuevo-admin" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Nuevo Administrador
                    </button>
                </div>
                
                <!-- Configuración del Sistema -->
                <div class="tab-pane fade" id="sistema" role="tabpanel">
                    <form id="form-config-sistema">
                        <div class="form-group">
                            <label for="backup_auto">Respaldo Automático</label>
                            <select class="form-control" id="backup_auto">
                                <option value="1">Activado</option>
                                <option value="0">Desactivado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="frecuencia_backup">Frecuencia de Respaldo</label>
                            <select class="form-control" id="frecuencia_backup">
                                <option value="daily">Diario</option>
                                <option value="weekly">Semanal</option>
                                <option value="monthly">Mensual</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button id="btn-backup-manual" class="btn btn-warning">
                            <i class="fas fa-database"></i> Generar Respaldo Ahora
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inicializar tabs manualmente
        $('.nav-tabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        // Formulario de configuración general
        $('#form-config-general').on('submit', function(e) {
            e.preventDefault();
            guardarConfigGeneral();
        });
        
        // Formulario de configuración del sistema
        $('#form-config-sistema').on('submit', function(e) {
            e.preventDefault();
            guardarConfigSistema();
        });
        
        // Botón de respaldo manual
        $('#btn-backup-manual').on('click', function(e) {
            e.preventDefault();
            generarRespaldo();
        });
        
        // Cargar datos iniciales
        cargarJornadas();
        cargarUsuariosAdmin();
    });
    
    function guardarConfigGeneral() {
        // Implementar guardado de configuración general
        showNotification('Configuración guardada exitosamente', 'success');
    }
    
    function guardarConfigSistema() {
        // Implementar guardado de configuración del sistema
        showNotification('Configuración del sistema actualizada', 'success');
    }
    
    function cargarJornadas() {
        // Implementar carga de jornadas
    }
    
    function cargarUsuariosAdmin() {
        // Implementar carga de usuarios administrativos
    }
    
    function generarRespaldo() {
        // Implementar generación de respaldo
        showNotification('Generando respaldo del sistema...', 'info');
        setTimeout(function() {
            showNotification('Respaldo completado exitosamente', 'success');
        }, 2000);
    }
</script>
@endsection 