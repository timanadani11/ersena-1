@extends('layouts.admin')

@section('title', 'Gestión de Aprendices - SENA Control de Asistencia')

@section('page-title', 'Listado de Aprendices')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/aprendices.css') }}">
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Buscador simple -->
    <div class="card fadeIn">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-search"></i> Buscar Aprendiz
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="search-aprendiz" placeholder="Ingrese documento del aprendiz...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de aprendices -->
    <div class="card fadeIn mt-4">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-user-graduate"></i> Listado de Aprendices
                <span class="badge bg-primary ms-2" id="contador-resultados">{{ $aprendices->total() }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Programa</th>
                            <th>Jornada</th>
                        </tr>
                    </thead>
                    <tbody id="aprendices-table-body">
                        @foreach($aprendices as $aprendiz)
                            <tr class="aprendiz-row" data-id="{{ $aprendiz->id }}">
                                <td>{{ $aprendiz->documento_identidad }}</td>
                                <td>{{ $aprendiz->nombres_completos }}</td>
                                <td>{{ $aprendiz->programaFormacion->nombre_programa ?? 'Sin asignar' }}</td>
                                <td>{{ $aprendiz->jornada->nombre ?? 'Sin asignar' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-4">
                {{ $aprendices->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal personalizado para detalles del aprendiz -->
<div class="custom-modal" id="aprendiz-modal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h3 class="custom-modal-title">
                <i class="fas fa-info-circle"></i> Información del Aprendiz
            </h3>
            <button type="button" class="close-modal-btn" id="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="custom-modal-body">
            <div class="aprendiz-details">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="profile-pic-container mb-3">
                            <div id="detail-profile-pic" class="detail-profile-pic">
                                <!-- La imagen o avatar por defecto se cargará aquí -->
                            </div>
                        </div>
                        <div id="qr-code-container" class="mt-3">
                            <!-- QR aquí -->
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="info-section">
                            <h4 id="detail-nombre" class="aprendiz-name"></h4>
                            <div class="info-group">
                                <div class="info-row">
                                    <span class="info-label"><i class="fas fa-id-card"></i> Documento:</span>
                                    <span id="detail-documento" class="info-value"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label"><i class="fas fa-envelope"></i> Correo:</span>
                                    <span id="detail-correo" class="info-value"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label"><i class="fas fa-graduation-cap"></i> Programa:</span>
                                    <span id="detail-programa" class="info-value"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label"><i class="fas fa-hashtag"></i> Ficha:</span>
                                    <span id="detail-ficha" class="info-value"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label"><i class="fas fa-clock"></i> Jornada:</span>
                                    <span id="detail-jornada" class="info-value"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pestañas -->
                <ul class="nav nav-tabs mt-4" id="aprendizTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="asistencia-tab" data-bs-toggle="tab" data-bs-target="#asistencia" 
                                type="button" role="tab" aria-selected="true">Asistencia</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="estadisticas-tab" data-bs-toggle="tab" data-bs-target="#estadisticas" 
                                type="button" role="tab" aria-selected="false">Estadísticas</button>
                    </li>
                </ul>
                
                <div class="tab-content p-3 border border-top-0 rounded-bottom" id="aprendizTabsContent">
                    <!-- Tab de asistencia -->
                    <div class="tab-pane fade show active" id="asistencia" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="asistencia-table-body">
                                    <!-- Se llenará con AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Tab de estadísticas -->
                    <div class="tab-pane fade" id="estadisticas" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stat-mini-card">
                                    <div class="stat-title">Asistencias</div>
                                    <div class="stat-value" id="stat-total-asistencias">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-mini-card">
                                    <div class="stat-title">Puntualidad</div>
                                    <div class="stat-value" id="stat-puntualidad">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-mini-card">
                                    <div class="stat-title">Llegadas tarde</div>
                                    <div class="stat-value" id="stat-llegadas-tarde">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-mini-card">
                                    <div class="stat-title">Salidas anticipadas</div>
                                    <div class="stat-value" id="stat-salidas-anticipadas">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overlay para modal -->
<div class="modal-overlay" id="modal-overlay"></div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Variable para controlar el tiempo entre búsquedas (debounce)
        let typingTimer;
        const doneTypingInterval = 300; // tiempo en ms
        
        // Búsqueda en tiempo real mientras se escribe
        $('#search-aprendiz').on('input', function() {
            clearTimeout(typingTimer);
            
            const documento = $(this).val().trim();
            
            // Solo buscar si hay al menos 3 caracteres o ninguno
            if (documento.length >= 3 || documento.length === 0) {
                typingTimer = setTimeout(function() {
                    buscarAprendices(documento);
                }, doneTypingInterval);
            }
        });
        
        // Hacer clic en una fila para ver detalles
        $(document).on('click', '.aprendiz-row', function() {
            const aprendizId = $(this).data('id');
            mostrarDetallesAprendiz(aprendizId);
            
            // Añadir clase a la fila seleccionada y quitarla de las demás
            $('.aprendiz-row').removeClass('selected-row');
            $(this).addClass('selected-row');
        });
        
        // Cerrar modal al hacer clic en X o en el overlay
        $('#close-modal, #modal-overlay').on('click', function() {
            cerrarModal();
        });
        
        // Cerrar modal con tecla ESC
        $(document).on('keyup', function(e) {
            if (e.key === "Escape") {
                cerrarModal();
            }
        });
        
        // Función para cerrar el modal
        function cerrarModal() {
            $('#aprendiz-modal').removeClass('show');
            $('#modal-overlay').removeClass('show');
            $('body').removeClass('modal-open');
        }
        
        // Función para buscar aprendices
        function buscarAprendices(documento) {
            $.ajax({
                url: '/admin/api/filtrar-aprendices',
                method: 'GET',
                data: { query: documento },
                success: function(response) {
                    actualizarTabla(response.aprendices);
                    $('#contador-resultados').text(response.aprendices.length);
                },
                error: function() {
                    alert('Error al buscar aprendices');
                }
            });
        }
        
        // Actualizar la tabla con los resultados
        function actualizarTabla(aprendices) {
            let html = '';
            
            if (aprendices.length === 0) {
                html = '<tr><td colspan="4" class="text-center">No se encontraron aprendices con ese documento</td></tr>';
            } else {
                aprendices.forEach(a => {
                    html += `
                        <tr class="aprendiz-row" data-id="${a.id}">
                            <td>${a.documento_identidad}</td>
                            <td>${a.nombres_completos}</td>
                            <td>${a.programa_formacion ? a.programa_formacion.nombre_programa : 'Sin asignar'}</td>
                            <td>${a.jornada ? a.jornada.nombre : 'Sin asignar'}</td>
                        </tr>
                    `;
                });
            }
            
            $('#aprendices-table-body').html(html);
        }
        
        // Función para mostrar detalles del aprendiz en modal personalizado
        function mostrarDetallesAprendiz(aprendizId) {
            $.ajax({
                url: `/admin/api/aprendices/${aprendizId}`,
                method: 'GET',
                success: function(aprendiz) {
                    // Información básica
                    $('#detail-nombre').text(aprendiz.nombres_completos);
                    $('#detail-documento').text(aprendiz.documento_identidad);
                    $('#detail-correo').text(aprendiz.correo);
                    
                    // Programa y jornada
                    $('#detail-programa').text(aprendiz.programa_formacion ? aprendiz.programa_formacion.nombre_programa : 'Sin asignar');
                    $('#detail-ficha').text(aprendiz.programa_formacion ? aprendiz.programa_formacion.numero_ficha : 'N/A');
                    $('#detail-jornada').text(aprendiz.jornada ? aprendiz.jornada.nombre : 'Sin asignar');
                    
                    // Foto de perfil
                    if (aprendiz.profile_photo) {
                        $('#detail-profile-pic').html(`<img src="${aprendiz.profile_photo}" alt="${aprendiz.nombres_completos}">`);
                    } else {
                        $('#detail-profile-pic').html(`<div class="default-avatar-large">${aprendiz.nombres_completos.charAt(0)}</div>`);
                    }
                    
                    // QR Code
                    if (aprendiz.qr_code) {
                        $('#qr-code-container').html(`
                            <div class="qr-code">
                                <img src="${aprendiz.qr_code}" alt="QR Code">
                            </div>
                            <p class="text-muted small">Código QR de asistencia</p>
                        `);
                    } else {
                        $('#qr-code-container').html(`
                            <div class="no-qr">
                                <p>QR no generado</p>
                            </div>
                        `);
                    }
                    
                    // Cargar asistencias
                    cargarAsistencias(aprendizId);
                    
                    // Cargar estadísticas
                    cargarEstadisticas(aprendizId);
                    
                    // Mostrar modal con animación
                    $('#modal-overlay').addClass('show');
                    $('#aprendiz-modal').addClass('show');
                    $('body').addClass('modal-open');
                },
                error: function() {
                    alert('Error al cargar los detalles del aprendiz');
                }
            });
        }
        
        // Cargar asistencias del aprendiz
        function cargarAsistencias(aprendizId) {
            $.ajax({
                url: `/admin/api/aprendices/${aprendizId}/asistencias`,
                method: 'GET',
                success: function(asistencias) {
                    let html = '';
                    if (asistencias.length === 0) {
                        html = '<tr><td colspan="4" class="text-center">No hay registros de asistencia</td></tr>';
                    } else {
                        asistencias.forEach(a => {
                            const fecha = new Date(a.fecha_hora).toLocaleDateString();
                            const hora = new Date(a.fecha_hora).toLocaleTimeString();
                            
                            let estadoClass = '';
                            let estadoText = '';
                            
                            if (a.tipo === 'entrada') {
                                estadoClass = a.fuera_de_horario ? 'text-warning' : 'text-success';
                                estadoText = a.fuera_de_horario ? 'Tarde' : 'A tiempo';
                            } else {
                                estadoClass = a.salida_anticipada ? 'text-danger' : 'text-success';
                                estadoText = a.salida_anticipada ? 'Anticipada' : 'Normal';
                            }
                            
                            html += `
                                <tr>
                                    <td>${fecha}</td>
                                    <td>${hora}</td>
                                    <td>${a.tipo === 'entrada' ? 'Entrada' : 'Salida'}</td>
                                    <td><span class="${estadoClass}">${estadoText}</span></td>
                                </tr>
                            `;
                        });
                    }
                    
                    $('#asistencia-table-body').html(html);
                }
            });
        }
        
        // Cargar estadísticas del aprendiz
        function cargarEstadisticas(aprendizId) {
            $.ajax({
                url: `/admin/api/aprendices/${aprendizId}/estadisticas`,
                method: 'GET',
                success: function(stats) {
                    // Actualizar estadísticas
                    $('#stat-total-asistencias').text(stats.total_asistencias || 0);
                    $('#stat-puntualidad').text((stats.porcentaje_puntualidad || 0) + '%');
                    $('#stat-llegadas-tarde').text(stats.llegadas_tarde || 0);
                    $('#stat-salidas-anticipadas').text(stats.salidas_anticipadas || 0);
                }
            });
        }
    });
</script>
@endsection 