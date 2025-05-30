@extends('layouts.admin')

@section('title', 'Configuración - SENA Control de Asistencia')

@section('page-title', 'Configuración del Sistema')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden" x-data="{ activeTab: 'general' }">
    <div class="border-b border-gray-100 px-5 py-4">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Configuración General</h3>
        </div>
        <p class="mt-1 text-sm text-gray-500">Configure los parámetros generales del sistema de asistencia.</p>
    </div>
    
    <div class="p-0">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                <button @click="activeTab = 'general'" 
                      :class="{'border-green-500 text-green-600': activeTab === 'general',
                              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'general'}"
                      class="py-4 px-6 border-b-2 font-medium text-sm whitespace-nowrap flex items-center space-x-2 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <span>General</span>
                </button>
                <button @click="activeTab = 'jornadas'" 
                      :class="{'border-green-500 text-green-600': activeTab === 'jornadas',
                              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'jornadas'}"
                      class="py-4 px-6 border-b-2 font-medium text-sm whitespace-nowrap flex items-center space-x-2 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Jornadas</span>
                </button>
                <button @click="activeTab = 'usuarios'" 
                      :class="{'border-green-500 text-green-600': activeTab === 'usuarios',
                              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'usuarios'}"
                      class="py-4 px-6 border-b-2 font-medium text-sm whitespace-nowrap flex items-center space-x-2 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Usuarios</span>
                </button>
                <button @click="activeTab = 'sistema'" 
                      :class="{'border-green-500 text-green-600': activeTab === 'sistema',
                              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'sistema'}"
                      class="py-4 px-6 border-b-2 font-medium text-sm whitespace-nowrap flex items-center space-x-2 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    <span>Sistema</span>
                </button>
            </nav>
        </div>
            
        <div class="p-6">
            <!-- Tab Configuración General -->
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <form id="form-config-general" class="space-y-4">
                    <div>
                        <label for="nombre_institucion" class="block text-sm font-medium text-gray-700">Nombre de la Institución</label>
                        <input type="text" id="nombre_institucion" value="Servicio Nacional de Aprendizaje - SENA" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="sede" class="block text-sm font-medium text-gray-700">Sede</label>
                        <input type="text" id="sede" value="Centro de Servicios y Gestión Empresarial" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="tolerancia_global" class="block text-sm font-medium text-gray-700">Tolerancia Global (minutos)</label>
                        <input type="number" id="tolerancia_global" value="15" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">Tiempo de tolerancia para las llegadas tarde (en minutos)</p>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Tab Jornadas -->
            <div x-show="activeTab === 'jornadas'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="space-y-6">
                    <div class="overflow-x-auto rounded-md shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Entrada</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Salida</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tolerancia (min)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Placeholder para carga de datos -->
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        <div class="flex justify-center items-center space-x-2">
                                            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>Cargando jornadas...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <button id="btn-nueva-jornada" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nueva Jornada
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Tab Usuarios -->
            <div x-show="activeTab === 'usuarios'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="space-y-6">
                    <p class="text-sm text-gray-500">Gestione los usuarios administrativos del sistema.</p>
                    <div class="usuarios-admin-list bg-gray-50 rounded-md p-4 min-h-[200px] flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm font-medium">Cargando usuarios administrativos...</p>
                        </div>
                    </div>
                    <div>
                        <button id="btn-nuevo-admin" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Nuevo Administrador
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Tab Sistema -->
            <div x-show="activeTab === 'sistema'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <form id="form-config-sistema" class="space-y-4">
                    <div>
                        <label for="backup_auto" class="block text-sm font-medium text-gray-700">Respaldo Automático</label>
                        <select id="backup_auto" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                            <option value="1">Activado</option>
                            <option value="0">Desactivado</option>
                        </select>
                    </div>
                    <div>
                        <label for="frecuencia_backup" class="block text-sm font-medium text-gray-700">Frecuencia de Respaldo</label>
                        <select id="frecuencia_backup" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                            <option value="daily">Diario</option>
                            <option value="weekly">Semanal</option>
                            <option value="monthly">Mensual</option>
                        </select>
                    </div>
                    <div class="pt-2 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Guardar Cambios
                        </button>
                        <button id="btn-backup-manual" type="button" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Generar Respaldo Ahora
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nueva Jornada (se mostrará con Alpine cuando sea necesario) -->
<div class="fixed inset-0 z-10 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true" id="modal-nueva-jornada">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Nueva Jornada
                        </h3>
                        <div class="mt-4 space-y-3">
                            <div>
                                <label for="nombre_jornada" class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input type="text" id="nombre_jornada" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Ej: Mañana, Tarde, Noche">
                            </div>
                            <div>
                                <label for="hora_entrada" class="block text-sm font-medium text-gray-700">Hora de Entrada</label>
                                <input type="time" id="hora_entrada" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="hora_salida" class="block text-sm font-medium text-gray-700">Hora de Salida</label>
                                <input type="time" id="hora_salida" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="tolerancia_jornada" class="block text-sm font-medium text-gray-700">Tolerancia (minutos)</label>
                                <input type="number" id="tolerancia_jornada" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm" value="15">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm" id="btn-guardar-jornada">
                    Guardar
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" id="btn-cancelar-jornada">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Formulario de configuración general
        document.getElementById('form-config-general')?.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarConfigGeneral();
        });
        
        // Formulario de configuración del sistema
        document.getElementById('form-config-sistema')?.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarConfigSistema();
        });
        
        // Botón de respaldo manual
        document.getElementById('btn-backup-manual')?.addEventListener('click', function(e) {
            e.preventDefault();
            generarRespaldo();
        });
        
        // Modal Nueva Jornada
        document.getElementById('btn-nueva-jornada')?.addEventListener('click', function() {
            document.getElementById('modal-nueva-jornada').classList.remove('hidden');
        });
        
        document.getElementById('btn-cancelar-jornada')?.addEventListener('click', function() {
            document.getElementById('modal-nueva-jornada').classList.add('hidden');
        });
        
        document.getElementById('btn-guardar-jornada')?.addEventListener('click', function() {
            // Aquí iría la lógica para guardar la jornada
            document.getElementById('modal-nueva-jornada').classList.add('hidden');
            // Mostrar notificación de éxito
            showNotification('Jornada guardada exitosamente', 'success');
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
        // Simulación de carga para frontend
        setTimeout(function() {
            const jornadasList = document.querySelector('.bg-white.divide-y.divide-gray-200');
            if (jornadasList) {
                jornadasList.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No hay jornadas configuradas</td></tr>';
            }
        }, 1000);
    }
    
    function cargarUsuariosAdmin() {
        // Implementar carga de usuarios administrativos
        // Simulación de carga para frontend
        setTimeout(function() {
            const usuariosList = document.querySelector('.usuarios-admin-list');
            if (usuariosList) {
                usuariosList.innerHTML = '<div class="text-center text-gray-500">No hay usuarios administrativos adicionales configurados</div>';
            }
        }, 1000);
    }
    
    function generarRespaldo() {
        // Implementar generación de respaldo
        showNotification('Generando respaldo del sistema...', 'info');
        setTimeout(function() {
            showNotification('Respaldo completado exitosamente', 'success');
        }, 2000);
    }
    
    function showNotification(message, type) {
        // Simple notification system - replace with your own
        console.log(`[${type.toUpperCase()}]: ${message}`);
        alert(message);
    }
</script>
@endpush
@endsection 