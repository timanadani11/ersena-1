@extends('layouts.admin')

@section('title', 'Escáner QR - SENA Control de Asistencia')

@section('page-title', 'Escáner de QR')

@section('content')
<div class="bg-white rounded-lg shadow-sm transition-opacity duration-300 animate-fadeIn" 
     x-data="scannerApp()">
    
    <!-- Mobile viewport meta tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Mobile optimized header -->
    <div class="border-b border-gray-100 p-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Escáner QR</h3>
        </div>
        
        <!-- Mobile toggle for info panel -->
        <button 
            @click="toggleInfoPanel" 
            class="md:hidden bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-lg transition-colors"
            :aria-expanded="showInfoPanel"
            aria-controls="infoPanel"
        >
            <svg x-show="!showInfoPanel" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <svg x-show="showInfoPanel" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    <!-- Mobile-first, stacked layout -->
    <div class="p-4">
        <!-- Scanner section - Always visible -->
        <div class="mb-4">
            <!-- QR Scanner - taller for mobile -->
            <div class="bg-black rounded-lg overflow-hidden h-[60vh] sm:h-[400px] flex items-center justify-center">
                <div id="reader" class="w-full h-full"></div>
            </div>
            
            <!-- Status indicator -->
            <div x-show="showStatus" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 :class="{
                    'bg-green-50 text-green-600': scanStatus === 'success',
                    'bg-red-50 text-red-600': scanStatus === 'error',
                    'bg-amber-50 text-amber-600': scanStatus === 'paused',
                    'bg-gray-50 text-gray-600': scanStatus === 'idle'
                 }"
                 class="mt-4 p-3 rounded-lg text-center font-medium">
                <div class="flex items-center justify-center space-x-2">
                    <template x-if="scanStatus === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>
                    <template x-if="scanStatus === 'error'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>
                    <template x-if="scanStatus === 'paused'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>
                    <span x-text="statusMessage"></span>
                </div>
            </div>
            
            <!-- Scanner controls - bigger buttons for touch -->
            <div class="flex space-x-3 mt-4">
                <button id="start-button" @click="iniciarEscanerQR" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg transition flex items-center justify-center text-base">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Iniciar Escaneo
                </button>
                <button id="stop-button" @click="detenerEscanerQR" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg transition flex items-center justify-center text-base">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                    </svg>
                    Detener
                </button>
            </div>
        </div>
        
        <!-- Info panel - Collapsible on mobile, always visible on desktop -->
        <div 
            id="infoPanel"
            class="md:block"
            x-show="showInfoPanel || window.innerWidth >= 768"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0" 
            x-transition:leave-end="opacity-0 transform translate-y-4"
            x-cloak
        >
            <!-- Manual search - larger input for mobile -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Buscar por documento
                </h4>
                <div class="flex">
                    <input type="text" id="documento" class="flex-1 border border-gray-300 rounded-l-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Número de documento" inputmode="numeric" pattern="[0-9]*">
                    <button @click="buscarAprendiz" id="btn-buscar" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-r-lg transition" 
                            :disabled="loading.buscar" 
                            :class="{'opacity-75': loading.buscar}">
                        <template x-if="!loading.buscar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </template>
                        <template x-if="loading.buscar">
                            <svg class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                    </button>
                </div>
            </div>
            
            <!-- Aprendiz Info Card - Will show when data is available -->
            <div x-show="currentUserData" x-transition.opacity class="bg-white rounded-lg p-4 border border-gray-200 mb-4 shadow-sm">
                <div class="flex items-center space-x-3 pb-3 border-b border-gray-100 mb-3">
                    <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        <span x-text="currentUserData ? currentUserData.user.nombres_completos.charAt(0) : ''"></span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800" x-text="currentUserData ? currentUserData.user.nombres_completos : ''"></h3>
                        <p class="text-xs text-gray-500" x-text="currentUserData ? currentUserData.user.documento_identidad : ''"></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs">Programa</p>
                        <p class="font-medium text-gray-700" x-text="currentUserData && currentUserData.user.programa_formacion ? currentUserData.user.programa_formacion.nombre_programa : 'N/A'"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Ficha</p>
                        <p class="font-medium text-gray-700" x-text="currentUserData && currentUserData.user.programa_formacion ? currentUserData.user.programa_formacion.numero_ficha : 'N/A'"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Nivel</p>
                        <p class="font-medium text-gray-700" x-text="currentUserData && currentUserData.user.programa_formacion ? currentUserData.user.programa_formacion.nivel_formacion : 'N/A'"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Jornada</p>
                        <p class="font-medium text-gray-700" x-text="currentUserData && currentUserData.user.jornada ? currentUserData.user.jornada.nombre : 'N/A'"></p>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button x-show="currentUserData && currentUserData.puede_registrar_entrada" 
                            @click="registrarAsistencia('entrada')" 
                            :disabled="loading.entrada"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg flex items-center justify-center text-sm">
                        <template x-if="!loading.entrada">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </template>
                        <template x-if="loading.entrada">
                            <svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        Registrar Entrada
                    </button>
                    <button x-show="currentUserData && currentUserData.puede_registrar_salida" 
                            @click="registrarAsistencia('salida')" 
                            :disabled="loading.salida"
                            class="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-2 px-3 rounded-lg flex items-center justify-center text-sm">
                        <template x-if="!loading.salida">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </template>
                        <template x-if="loading.salida">
                            <svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        Registrar Salida
                    </button>
                </div>
            </div>
            
            <!-- Recent scans -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Escaneos Recientes
                </h4>
                <div id="recent-scans" class="overflow-y-auto max-h-[200px] sm:max-h-[300px] space-y-2">
                    <template x-if="recentScans.length === 0">
                        <div class="text-center text-gray-400 py-4">
                            <p>No hay escaneos recientes</p>
                        </div>
                    </template>
                    
                    <template x-for="scan in recentScans" :key="scan.id">
                        <div class="bg-white p-3 rounded border border-gray-100 shadow-sm flex items-center">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-gray-800 truncate" x-text="scan.name"></p>
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <span x-text="scan.documento"></span>
                                    <span class="inline-block w-1 h-1 rounded-full bg-gray-300 mx-2"></span>
                                    <span x-text="scan.time"></span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <span :class="{
                                    'bg-blue-100 text-blue-800': scan.type === 'entrada',
                                    'bg-amber-100 text-amber-800': scan.type === 'salida'
                                }" class="px-2 py-1 rounded-full text-xs font-medium" x-text="scan.type === 'entrada' ? 'Entrada' : 'Salida'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for incorrect schedule -->
<div x-show="showWrongShiftModal" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Horario incorrecto</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">El aprendiz está intentando ingresar fuera de su jornada asignada.</p>
                            <div class="mt-3 bg-amber-50 border border-amber-200 rounded-md p-3 text-sm">
                                <p class="text-amber-800"><span class="font-medium">Hora actual:</span> <span x-text="currentTime"></span></p>
                                <p class="text-amber-800 mt-1"><span class="font-medium">Jornada asignada:</span> <span x-text="assignedSchedule"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <form @submit.prevent="submitWrongShiftForm">
                    <div class="mb-4">
                        <label for="motivo-entrada" class="block text-sm font-medium text-gray-700 mb-1">Motivo de ingreso fuera de horario:</label>
                        <select x-model="wrongShiftForm.motivo" id="motivo-entrada" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Seleccione un motivo</option>
                            <option value="coordinacion">Autorización de Coordinación</option>
                            <option value="recuperacion">Recuperación de tiempo</option>
                            <option value="actividad_especial">Actividad especial</option>
                            <option value="otro">Otro motivo</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="observaciones-entrada" class="block text-sm font-medium text-gray-700 mb-1">Observaciones:</label>
                        <textarea x-model="wrongShiftForm.observaciones" id="observaciones-entrada" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" placeholder="Detalle el motivo del ingreso fuera de horario"></textarea>
                    </div>
                    
                    <div x-show="wrongShiftForm.motivo !== 'recuperacion'" class="mb-4">
                        <label for="foto-autorizacion" class="block text-sm font-medium text-gray-700 mb-1">Foto de autorización:</label>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="document.getElementById('foto-autorizacion').click()" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-5 h-5 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Tomar foto
                            </button>
                            <span x-text="wrongShiftForm.fileName || 'Ningún archivo seleccionado'" class="text-sm text-gray-500"></span>
                            <input type="file" id="foto-autorizacion" class="hidden" accept="image/*" capture="camera" @change="handleFileChange">
                        </div>
                        <div x-show="wrongShiftForm.imagePreview" class="mt-3">
                            <img :src="wrongShiftForm.imagePreview" alt="Vista previa" class="max-w-full h-auto rounded-md">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeWrongShiftModal" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center" :disabled="loading.wrongShiftForm">
                            <template x-if="loading.wrongShiftForm">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            Autorizar entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.audio')

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out;
    }
    
    /* Hide element with x-cloak until Alpine.js loads */
    [x-cloak] { 
        display: none !important; 
    }
    
    /* Improve video element sizing for full camera view */
    #reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }
    
    /* Overrides for scanner UI to be more mobile-friendly */
    #reader__scan_region {
        min-height: 100% !important;
    }
    
    #reader__dashboard_section_csr button {
        padding: 8px 12px !important;
        font-size: 14px !important;
        border-radius: 6px !important;
    }
    
    /* Remove any fixed width/height constraints from QR scanner elements */
    #reader__scan_region img {
        width: auto !important;
        height: auto !important;
        max-width: 60% !important;
    }
</style>

<script src="https://unpkg.com/html5-qrcode@2.3.8"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('scannerApp', () => ({
            // State variables
            html5QrCode: null,
            lastScanned: null,
            scanActive: true,
            scanCooldown: false,
            currentUserData: null,
            scanStatus: 'idle',
            statusMessage: 'Esperando código QR...',
            showStatus: true,
            showInfoPanel: window.innerWidth >= 768,
            showWrongShiftModal: false,
            recentScans: [],
            COOLDOWN_TIME: 5000,
            currentTime: '',
            assignedSchedule: '',
            
            loading: {
                buscar: false,
                entrada: false,
                salida: false,
                wrongShiftForm: false
            },
            
            wrongShiftForm: {
                motivo: '',
                observaciones: '',
                fileName: '',
                imagePreview: null,
                file: null
            },
            
            init() {
                // Initialize scanner on page load
                setTimeout(() => this.iniciarEscanerQR(), 500);
                
                // Setup lifecycle events
                window.addEventListener('beforeunload', () => this.detenerEscanerQR());
                
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        this.detenerEscanerQR();
                    } else {
                        this.iniciarEscanerQR();
                    }
                });
                
                // Setup resize and orientation change events
                window.addEventListener('resize', () => {
                    if (!document.hidden) {
                        this.detenerEscanerQR();
                        setTimeout(() => this.iniciarEscanerQR(), 500);
                        
                        if (window.innerWidth >= 768 && !this.showInfoPanel) {
                            this.showInfoPanel = true;
                        }
                    }
                });
                
                window.addEventListener('orientationchange', () => {
                    if (!document.hidden) {
                        this.detenerEscanerQR();
                        setTimeout(() => this.iniciarEscanerQR(), 500);
                    }
                });
                
                // Set up CSRF for AJAX requests
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            },
            
            // Toggle info panel visibility (for mobile)
            toggleInfoPanel() {
                this.showInfoPanel = !this.showInfoPanel;
            },
            
            // Update scanner status
            updateStatus(status, message) {
                this.scanStatus = status;
                this.statusMessage = message;
                this.showStatus = true;
            },
            
            // QR Scanner Configuration
            getQrConfig() {
                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    disableFlip: false,
                    colorMode: "always",  // Force color camera instead of grayscale
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.CODE_128
                    ],
                    rememberLastUsedCamera: true,
                    showTorchButtonIfSupported: true,
                    showZoomSliderIfSupported: true,
                    defaultZoomValueIfSupported: 2  // Add default zoom for better focusing on mobile
                };
                
                // Apply mobile-specific configurations
                if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                    config.aspectRatio = window.innerHeight > window.innerWidth ? 1.33 : 0.75;
                    config.videoConstraints = {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: { ideal: "environment" },
                        advanced: [{ zoom: 2.0 }]
                    };
                }
                
                return config;
            },
            
            // Initialize QR Scanner
            iniciarEscanerQR() {
                console.log("Iniciando escáner QR...");
                
                // Stop existing scanner if any
                this.detenerEscanerQR();
                
                // Create new instance
                this.html5QrCode = new Html5Qrcode("reader");
                
                // Check for camera API availability
                if (typeof Html5Qrcode.getCameras !== 'function') {
                    this.updateStatus('error', 'La API de cámaras no está disponible en este navegador');
                    return;
                }
                
                Html5Qrcode.getCameras()
                    .then(devices => {
                        if (devices && devices.length) {
                            // Prefer rear camera on mobile devices
                            const camaraTrasera = devices.find(device => /(back|rear|trasera|environment)/i.test(device.label || ''));
                            const camaraId = camaraTrasera ? camaraTrasera.id : devices[0].id;
                            
                            console.log("Cámaras disponibles:", devices);
                            console.log("Seleccionando cámara:", camaraId);
                            
                            const qrConfig = this.getQrConfig();
                            
                            this.html5QrCode.start(
                                { deviceId: { exact: camaraId } },
                                qrConfig,
                                (decodedText) => this.onScanSuccess(decodedText),
                                (errorMessage) => {
                                    // Silent errors during scanning
                                    console.log("Error during scanning (normal):", errorMessage);
                                }
                            ).catch((err) => {
                                console.error(`Error starting scanner: ${err}`);
                                this.updateStatus('error', 'No se pudo acceder a la cámara. Verifique los permisos');
                                
                                // Try with basic configuration as fallback
                                this.html5QrCode.start(
                                    { facingMode: "environment" },
                                    { fps: 10, qrbox: 250, colorMode: "always" },
                                    (decodedText) => this.onScanSuccess(decodedText),
                                    () => {}
                                ).catch(e => console.error("Error in fallback:", e));
                            });
                        } else {
                            console.error("No camera devices found");
                            this.updateStatus('error', 'No se detectaron cámaras en el dispositivo');
                        }
                    })
                    .catch(err => {
                        console.error(`Error enumerating cameras: ${err}`);
                        this.updateStatus('error', 'Error al acceder a las cámaras');
                    });
            },
            
            // Stop QR Scanner
            detenerEscanerQR() {
                if (this.html5QrCode) {
                    this.html5QrCode.stop().catch(err => {
                        console.error(`Error stopping scanner: ${err}`);
                    });
                }
            },
            
            // Pause scanner
            pausarEscaner() {
                this.scanActive = false;
                this.updateStatus('paused', 'Escáner pausado');
            },
            
            // Resume scanner
            reanudarEscaner() {
                this.scanActive = true;
                this.updateStatus('idle', 'Esperando código QR...');
            },
            
            // Process scanned code
            onScanSuccess(decodedText) {
                // If scanner paused or cooldown active, ignore
                if (!this.scanActive || (this.scanCooldown && this.lastScanned === decodedText)) {
                    return;
                }
                
                // Register code and activate cooldown
                this.lastScanned = decodedText;
                this.scanCooldown = true;
                this.pausarEscaner();
                
                // Update UI
                this.updateStatus('success', 'Código detectado, procesando...');
                
                // Vibrate device if available
                if (navigator.vibrate) {
                    navigator.vibrate(200);
                }
                
                this.showNotification('Código QR escaneado, procesando...', 'info');
                
                // Process after a moment
                setTimeout(() => this.buscarAprendizPorQR(decodedText), 500);
                
                // Reset cooldown after defined time
                setTimeout(() => {
                    this.scanCooldown = false;
                    this.reanudarEscaner();
                }, this.COOLDOWN_TIME);
            },
            
            // Show notification
            showNotification(message, type = 'info') {
                // You can implement a toast notification system here
                console.log(`[${type.toUpperCase()}] ${message}`);
                
                // Use browser notification if available
                if ("Notification" in window && Notification.permission === "granted") {
                    new Notification("SENA Asistencias", {
                        body: message,
                        icon: "/favicon.ico"
                    });
                }
            },
            
            // Search apprentice by document number
            buscarAprendiz() {
                const documento = document.getElementById('documento').value;
                if (!documento) {
                    this.showNotification('Ingrese un número de documento', 'error');
                    return;
                }
                
                this.loading.buscar = true;
                
                $.ajax({
                    url: '{{ route("admin.verificar-asistencia") }}',
                    method: 'POST',
                    data: { documento_identidad: documento },
                    success: (response) => {
                        this.loading.buscar = false;
                        this.mostrarInformacionAprendiz(response);
                    },
                    error: (error) => {
                        this.loading.buscar = false;
                        this.showNotification('Error: ' + (error.responseJSON?.error || 'Aprendiz no encontrado'), 'error');
                    }
                });
            },
            
            // Search apprentice by QR code
            buscarAprendizPorQR(qrCode) {
                $.ajax({
                    url: '{{ route("admin.buscar-por-qr") }}',
                    method: 'POST',
                    data: { qr_code: qrCode },
                    success: (response) => {
                        this.mostrarInformacionAprendiz(response);
                        
                        // Register attendance automatically
                        setTimeout(() => this.registrarAsistenciaAutomatica(response), 1000);
                    },
                    error: (error) => {
                        this.showNotification('Error: ' + (error.responseJSON?.error || 'Código QR no válido'), 'error');
                        this.updateStatus('error', 'Error: Código QR no válido');
                    }
                });
            },
            
            // Show apprentice information
            mostrarInformacionAprendiz(data) {
                // Save data for later use
                this.currentUserData = data;
                
                // Update status based on attendance status
                if (data.puede_registrar_entrada) {
                    this.updateStatus('success', 'Aprendiz identificado - Puede registrar entrada');
                } else if (data.puede_registrar_salida) {
                    this.updateStatus('success', 'Aprendiz identificado - Puede registrar salida');
                } else {
                    this.updateStatus('idle', 'Aprendiz identificado - Sin acciones pendientes');
                }
            },
            
            // Register attendance automatically
            registrarAsistenciaAutomatica(data) {
                if (data.puede_registrar_entrada) {
                    this.registrarAsistencia('entrada');
                } else if (data.puede_registrar_salida) {
                    this.registrarAsistencia('salida');
                } else {
                    this.showNotification('Ya se registraron todas las asistencias para hoy', 'info');
                }
            },
            
            // Register attendance
            registrarAsistencia(tipo) {
                if (!this.currentUserData) return;
                
                const documento = this.currentUserData.user.documento_identidad;
                
                // Check if reason required for entry
                if (tipo === 'entrada' && this.currentUserData.requiere_motivo_entrada) {
                    this.abrirModalHorarioIncorrecto(documento);
                    return;
                }
                
                // Currently not handling early exit
                
                // Show loading
                this.loading[tipo] = true;
                
                // Basic data for request
                let requestData = {
                    documento_identidad: documento,
                    tipo: tipo
                };
                
                // Make request
                $.ajax({
                    url: '{{ route("admin.registrar-asistencia") }}',
                    method: 'POST',
                    data: requestData,
                    success: (response) => {
                        // Hide loading
                        this.loading[tipo] = false;
                        
                        // Success message
                        const mensaje = tipo === 'entrada' ? 'Entrada registrada correctamente' : 'Salida registrada correctamente';
                        this.showNotification(mensaje, 'success');
                        
                        // Update interface
                        this.updateStatus('success', mensaje);
                        
                        // Update user data
                        if (tipo === 'entrada') {
                            this.currentUserData.puede_registrar_entrada = false;
                            this.currentUserData.puede_registrar_salida = true;
                        } else {
                            this.currentUserData.puede_registrar_salida = false;
                        }
                        
                        // Add to recent scans
                        this.addToRecentScans({
                            id: Date.now(),
                            name: this.currentUserData.user.nombres_completos,
                            documento: this.currentUserData.user.documento_identidad,
                            type: tipo,
                            time: new Date().toLocaleTimeString('es-CO', {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true
                            })
                        });
                    },
                    error: (error) => {
                        // Hide loading
                        this.loading[tipo] = false;
                        
                        // Show error
                        const errorMsg = error.responseJSON?.error || 'Error al registrar asistencia';
                        this.showNotification(errorMsg, 'error');
                        
                        // Update interface
                        this.updateStatus('error', 'Error: ' + errorMsg);
                    }
                });
            },
            
            // Add scan to recent scans list
            addToRecentScans(scan) {
                this.recentScans.unshift(scan);
                
                // Keep only latest 10 scans
                if (this.recentScans.length > 10) {
                    this.recentScans.pop();
                }
            },
            
            // Open wrong shift modal
            abrirModalHorarioIncorrecto(documento) {
                // Reset form
                this.wrongShiftForm = {
                    motivo: '',
                    observaciones: '',
                    fileName: '',
                    imagePreview: null,
                    file: null
                };
                
                // Set current time
                const ahora = new Date();
                this.currentTime = ahora.toLocaleTimeString('es-CO', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                
                // Set assigned schedule
                let horarioTexto = 'No definido';
                let jornadaActual = this.currentUserData?.user?.jornada;
                
                if (jornadaActual) {
                    horarioTexto = jornadaActual.nombre;
                    if (jornadaActual.hora_inicio && jornadaActual.hora_fin) {
                        horarioTexto += ` (${jornadaActual.hora_inicio} - ${jornadaActual.hora_fin})`;
                    }
                }
                
                this.assignedSchedule = horarioTexto;
                
                // Show modal
                this.showWrongShiftModal = true;
            },
            
            // Close wrong shift modal
            closeWrongShiftModal() {
                this.showWrongShiftModal = false;
            },
            
            // Handle file change for authorization photo
            handleFileChange(event) {
                const file = event.target.files[0];
                if (file) {
                    this.wrongShiftForm.fileName = file.name;
                    this.wrongShiftForm.file = file;
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.wrongShiftForm.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.wrongShiftForm.fileName = '';
                    this.wrongShiftForm.imagePreview = null;
                    this.wrongShiftForm.file = null;
                }
            },
            
            // Submit wrong shift form
            submitWrongShiftForm() {
                if (!this.wrongShiftForm.motivo) {
                    this.showNotification('Debe seleccionar un motivo', 'error');
                    return;
                }
                
                this.loading.wrongShiftForm = true;
                
                // Create form data
                const formData = new FormData();
                formData.append('documento_identidad', this.currentUserData.user.documento_identidad);
                formData.append('tipo', 'entrada');
                formData.append('fuera_de_horario', '1');
                formData.append('motivo', this.wrongShiftForm.motivo);
                formData.append('observaciones', this.wrongShiftForm.observaciones || '');
                
                if (this.wrongShiftForm.file) {
                    formData.append('foto_autorizacion', this.wrongShiftForm.file);
                }
                
                // Submit form
                $.ajax({
                    url: '{{ route("admin.registrar-asistencia") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        this.loading.wrongShiftForm = false;
                        this.closeWrongShiftModal();
                        this.showNotification('Entrada autorizada correctamente', 'success');
                        
                        // Update interface
                        this.updateStatus('success', 'Entrada autorizada correctamente');
                        
                        // Update user data
                        this.currentUserData.puede_registrar_entrada = false;
                        this.currentUserData.puede_registrar_salida = true;
                        
                        // Add to recent scans
                        this.addToRecentScans({
                            id: Date.now(),
                            name: this.currentUserData.user.nombres_completos,
                            documento: this.currentUserData.user.documento_identidad,
                            type: 'entrada',
                            time: new Date().toLocaleTimeString('es-CO', {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true
                            })
                        });
                    },
                    error: (error) => {
                        this.loading.wrongShiftForm = false;
                        const errorMsg = error.responseJSON?.error || 'Error al autorizar entrada';
                        this.showNotification(errorMsg, 'error');
                    }
                });
            }
        }));
    });
</script>
@endsection 