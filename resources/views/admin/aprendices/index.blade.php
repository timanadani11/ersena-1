@extends('layouts.admin')

@section('title', 'Gestión de Aprendices - SENA Control de Asistencia')

@section('page-title', 'Listado de Aprendices')

@section('content')
<div class="space-y-6">
    <!-- Buscador simple -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700">Buscar Aprendiz</h3>
        </div>
        <div class="w-full max-w-lg mx-auto">
            <div class="flex items-center border rounded-lg overflow-hidden">
                <div class="bg-gray-100 p-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                    </svg>
                </div>
                <input type="text" id="search-aprendiz" class="w-full p-2 outline-none" placeholder="Ingrese documento del aprendiz...">
            </div>
        </div>
    </div>

    <!-- Listado de aprendices -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="border-b p-4 flex justify-between items-center">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-700">Listado de Aprendices</h3>
                <span class="ml-2 px-2 py-1 text-xs font-semibold text-white bg-blue-600 rounded-full" id="contador-resultados">{{ $aprendices->total() }}</span>
            </div>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Documento</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Nombre</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Programa</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Jornada</th>
                        </tr>
                    </thead>
                    <tbody id="aprendices-table-body">
                        @foreach($aprendices as $aprendiz)
                            <tr class="border-b hover:bg-gray-50 cursor-pointer" data-id="{{ $aprendiz->id }}">
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $aprendiz->documento_identidad }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $aprendiz->nombres_completos }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $aprendiz->programaFormacion->nombre_programa ?? 'Sin asignar' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $aprendiz->jornada->nombre ?? 'Sin asignar' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="mt-4 flex justify-center">
                {{ $aprendices->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal personalizado para detalles del aprendiz -->
<div x-data="{ open: false, activeTab: 'asistencia', aprendizId: null }" 
     x-init="$watch('open', value => value ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))"
     @open-modal.window="open = true; aprendizId = $event.detail.id; if(aprendizId) loadAprendizDetails(aprendizId)"
     class="relative z-50">
    
    <!-- Overlay de fondo -->
    <div x-show="open" 
         class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false">
    </div>
    
    <!-- Modal -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Información del Aprendiz
                    </h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-6">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 flex flex-col items-center mb-6 md:mb-0">
                            <div class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center mb-4" id="detail-profile-pic">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div id="qr-code-container" class="mt-3 p-2 border rounded bg-white"></div>
                        </div>
                        
                        <div class="md:w-2/3 md:pl-6">
                            <h4 id="detail-nombre" class="text-xl font-semibold mb-4"></h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                    </svg>
                                    <span class="text-gray-600">Documento: </span>
                                    <span id="detail-documento" class="ml-1 font-medium"></span>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-gray-600">Correo: </span>
                                    <span id="detail-correo" class="ml-1 font-medium"></span>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                                    </svg>
                                    <span class="text-gray-600">Programa: </span>
                                    <span id="detail-programa" class="ml-1 font-medium"></span>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                    </svg>
                                    <span class="text-gray-600">Ficha: </span>
                                    <span id="detail-ficha" class="ml-1 font-medium"></span>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-gray-600">Jornada: </span>
                                    <span id="detail-jornada" class="ml-1 font-medium"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabs -->
                    <div class="mt-6">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-6">
                                <button @click="activeTab = 'asistencia'" 
                                        :class="{'border-blue-500 text-blue-600': activeTab === 'asistencia', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'asistencia'}"
                                        class="border-b-2 py-2 px-1 text-sm font-medium">
                                    Asistencia
                                </button>
                                <button @click="activeTab = 'estadisticas'"
                                        :class="{'border-blue-500 text-blue-600': activeTab === 'estadisticas', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'estadisticas'}"
                                        class="border-b-2 py-2 px-1 text-sm font-medium">
                                    Estadísticas
                                </button>
                            </nav>
                        </div>
                        
                        <div class="py-4">
                            <!-- Tab Asistencia -->
                            <div x-show="activeTab === 'asistencia'" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="asistencia-table-body" class="divide-y divide-gray-200">
                                        <!-- Se llenará con JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Tab Estadísticas -->
                            <div x-show="activeTab === 'estadisticas'">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-white p-4 rounded-lg border">
                                        <div class="text-sm font-medium text-gray-500">Asistencias</div>
                                        <div class="text-2xl font-semibold mt-1" id="stat-total-asistencias">-</div>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border">
                                        <div class="text-sm font-medium text-gray-500">Puntualidad</div>
                                        <div class="text-2xl font-semibold mt-1" id="stat-puntualidad">-</div>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border">
                                        <div class="text-sm font-medium text-gray-500">Llegadas tarde</div>
                                        <div class="text-2xl font-semibold mt-1" id="stat-llegadas-tarde">-</div>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border">
                                        <div class="text-sm font-medium text-gray-500">Salidas anticipadas</div>
                                        <div class="text-2xl font-semibold mt-1" id="stat-salidas-anticipadas">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variable para controlar el tiempo entre búsquedas (debounce)
        let typingTimer;
        const doneTypingInterval = 500;
        const searchInput = document.getElementById('search-aprendiz');
        
        // Evento de escritura en el campo de búsqueda
        searchInput.addEventListener('keyup', function() {
            clearTimeout(typingTimer);
            if (searchInput.value) {
                typingTimer = setTimeout(buscarAprendiz, doneTypingInterval);
            }
        });
        
        // Evento de clic en filas de aprendices
        document.querySelectorAll('#aprendices-table-body tr').forEach(row => {
            row.addEventListener('click', function() {
                const aprendizId = this.dataset.id;
                mostrarDetallesAprendiz(aprendizId);
            });
        });
        
        // Función para buscar aprendiz
        function buscarAprendiz() {
            const documento = searchInput.value.trim();
            
            if (documento.length < 3) return;
            
            fetch(`{{ route('admin.aprendices.buscar') }}?documento=${documento}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                actualizarTablaAprendices(data.aprendices);
                document.getElementById('contador-resultados').textContent = data.total;
            })
            .catch(error => {
                console.error('Error al buscar aprendiz:', error);
                showNotification('Error al buscar aprendiz', 'error');
            });
        }
        
        // Función para actualizar la tabla de aprendices
        function actualizarTablaAprendices(aprendices) {
            const tbody = document.getElementById('aprendices-table-body');
            tbody.innerHTML = '';
            
            if (aprendices.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="4" class="px-4 py-3 text-center text-gray-500">No se encontraron resultados</td>`;
                tbody.appendChild(tr);
                return;
            }
            
            aprendices.forEach(aprendiz => {
                const tr = document.createElement('tr');
                tr.classList.add('border-b', 'hover:bg-gray-50', 'cursor-pointer');
                tr.dataset.id = aprendiz.id;
                
                tr.innerHTML = `
                    <td class="px-4 py-3 text-sm text-gray-700">${aprendiz.documento_identidad}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">${aprendiz.nombres_completos}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">${aprendiz.programa?.nombre_programa || 'Sin asignar'}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">${aprendiz.jornada?.nombre || 'Sin asignar'}</td>
                `;
                
                tr.addEventListener('click', function() {
                    mostrarDetallesAprendiz(aprendiz.id);
                });
                
                tbody.appendChild(tr);
            });
        }
        
        // Función para mostrar detalles del aprendiz
        function mostrarDetallesAprendiz(aprendizId) {
            // Disparar evento para abrir el modal con Alpine.js
            window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: aprendizId } }));
        }
        
        // Función para cargar detalles del aprendiz (esta función será accesible desde Alpine.js)
        window.loadAprendizDetails = function(aprendizId) {
            fetch(`{{ route('admin.aprendices.detalles', ['id' => ':id']) }}`.replace(':id', aprendizId), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Llenar información básica
                document.getElementById('detail-nombre').textContent = data.aprendiz.nombres_completos;
                document.getElementById('detail-documento').textContent = data.aprendiz.documento_identidad;
                document.getElementById('detail-correo').textContent = data.aprendiz.correo_electronico || 'No disponible';
                document.getElementById('detail-programa').textContent = data.aprendiz.programa?.nombre_programa || 'Sin asignar';
                document.getElementById('detail-ficha').textContent = data.aprendiz.ficha?.numero_ficha || 'Sin asignar';
                document.getElementById('detail-jornada').textContent = data.aprendiz.jornada?.nombre || 'Sin asignar';
                
                // Generar QR
                generarQR(data.aprendiz.documento_identidad);
                
                // Llenar tabla de asistencias
                llenarTablaAsistencias(data.asistencias);
                
                // Actualizar estadísticas
                actualizarEstadisticas(data.estadisticas);
            })
            .catch(error => {
                console.error('Error al cargar detalles del aprendiz:', error);
                showNotification('Error al cargar detalles del aprendiz', 'error');
            });
        };
        
        // Función para generar código QR
        function generarQR(documento) {
            const qrContainer = document.getElementById('qr-code-container');
            qrContainer.innerHTML = '';
            
            // Usar la librería QRCode incluida en el proyecto
            new QRCode(qrContainer, {
                text: documento,
                width: 128,
                height: 128,
                colorDark: "#000",
                colorLight: "#fff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
        
        // Función para llenar tabla de asistencias
        function llenarTablaAsistencias(asistencias) {
            const tbody = document.getElementById('asistencia-table-body');
            tbody.innerHTML = '';
            
            if (asistencias.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="4" class="px-3 py-2 text-center text-gray-500">No hay registros de asistencia</td>`;
                tbody.appendChild(tr);
                return;
            }
            
            asistencias.forEach(asistencia => {
                const tr = document.createElement('tr');
                
                // Determinar el estado con clase de color
                let estadoClase = '';
                switch(asistencia.estado) {
                    case 'A tiempo':
                        estadoClase = 'text-green-600';
                        break;
                    case 'Tarde':
                        estadoClase = 'text-yellow-600';
                        break;
                    case 'Ausente':
                        estadoClase = 'text-red-600';
                        break;
                    default:
                        estadoClase = 'text-gray-600';
                }
                
                tr.innerHTML = `
                    <td class="px-3 py-2 text-xs text-gray-700">${formatDate(asistencia.fecha)}</td>
                    <td class="px-3 py-2 text-xs text-gray-700">${asistencia.hora}</td>
                    <td class="px-3 py-2 text-xs text-gray-700">${asistencia.tipo}</td>
                    <td class="px-3 py-2 text-xs font-medium ${estadoClase}">${asistencia.estado}</td>
                `;
                
                tbody.appendChild(tr);
            });
        }
        
        // Función para actualizar estadísticas
        function actualizarEstadisticas(estadisticas) {
            document.getElementById('stat-total-asistencias').textContent = estadisticas.total_asistencias || '0';
            document.getElementById('stat-puntualidad').textContent = (estadisticas.puntualidad || '0') + '%';
            document.getElementById('stat-llegadas-tarde').textContent = estadisticas.llegadas_tarde || '0';
            document.getElementById('stat-salidas-anticipadas').textContent = estadisticas.salidas_anticipadas || '0';
        }
        
        // Función para formatear fecha
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        }
    });
</script>
@endsection 