@extends('layouts.admin')

@section('title', 'Reportes - SENA Control de Asistencia')

@section('page-title', 'Reportes de Asistencia')

@section('content')
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-chart-bar"></i> Generación de Reportes
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.reportes.pdf') }}" id="reportForm">
            @csrf
            <div class="row">
                <div class="col-md-12 mb-4">
                    <h4>Tipo de Reporte</h4>
                    <div class="btn-group report-type-selector" role="group">
                        <input type="radio" class="btn-check" name="tipo_reporte" id="tipo_diario" value="diario" checked>
                        <label class="btn btn-outline-primary" for="tipo_diario">Diario</label>

                        <input type="radio" class="btn-check" name="tipo_reporte" id="tipo_semanal" value="semanal">
                        <label class="btn btn-outline-primary" for="tipo_semanal">Semanal</label>

                        <input type="radio" class="btn-check" name="tipo_reporte" id="tipo_mensual" value="mensual">
                        <label class="btn btn-outline-primary" for="tipo_mensual">Mensual</label>

                        <input type="radio" class="btn-check" name="tipo_reporte" id="tipo_personalizado" value="personalizado">
                        <label class="btn btn-outline-primary" for="tipo_personalizado">Personalizado</label>

                        <input type="radio" class="btn-check" name="tipo_reporte" id="tipo_programa" value="programa">
                        <label class="btn btn-outline-primary" for="tipo_programa">Por Programa</label>

                        <input type="radio" class="btn-check" name="tipo_reporte" id="tipo_jornada" value="jornada">
                        <label class="btn btn-outline-primary" for="tipo_jornada">Por Jornada</label>

                        <input type="radio" class="btn-check" name="tipo_reporte" id="tipo_aprendiz" value="aprendiz">
                        <label class="btn btn-outline-primary" for="tipo_aprendiz">Por Aprendiz</label>
                    </div>
                </div>

                <!-- Filtros personalizados - Solo aparecerán cuando se seleccione "Personalizado" -->
                <div class="col-md-6 mb-3 filtro-personalizado" style="display: none;">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha inicio:</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
                    </div>
                </div>

                <div class="col-md-6 mb-3 filtro-personalizado" style="display: none;">
                    <div class="form-group">
                        <label for="fecha_fin">Fecha fin:</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control">
                    </div>
                </div>

                <!-- Filtros de programa - Solo aparecerán cuando se seleccione "Por Programa" -->
                <div class="col-md-12 mb-3 filtro-programa" style="display: none;">
                    <div class="form-group">
                        <label for="programa_id">Programa de Formación:</label>
                        <select name="programa_id" id="programa_id" class="form-control">
                            <option value="">Todos los programas</option>
                            @foreach($programas as $programa)
                                <option value="{{ $programa->id }}">
                                    {{ $programa->nombre_programa }} ({{ $programa->numero_ficha ?? 'Sin ficha' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filtros de jornada - Solo aparecerán cuando se seleccione "Por Jornada" -->
                <div class="col-md-12 mb-3 filtro-jornada" style="display: none;">
                    <div class="form-group">
                        <label for="jornada_id">Jornada:</label>
                        <select name="jornada_id" id="jornada_id" class="form-control">
                            <option value="">Todas las jornadas</option>
                            @foreach($jornadas as $jornada)
                                <option value="{{ $jornada->id }}">{{ $jornada->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filtros de aprendiz - Solo aparecerán cuando se seleccione "Por Aprendiz" -->
                <div class="col-md-12 mb-3 filtro-aprendiz" style="display: none;">
                    <div class="form-group">
                        <label for="aprendiz_search">Buscar aprendiz:</label>
                        <div class="input-group">
                            <input type="text" id="aprendiz_search" class="form-control" placeholder="Nombre o documento...">
                            <button type="button" id="btn-search-aprendiz" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div id="aprendices-results" class="mt-2"></div>
                        <input type="hidden" name="aprendiz_id" id="aprendiz_id" value="">
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label for="tipo">Tipo de registro:</label>
                        <select name="tipo" id="tipo" class="form-control">
                            <option value="">Ambos tipos</option>
                            <option value="entrada">Solo entradas</option>
                            <option value="salida">Solo salidas</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="form-group">
                        <label>Información adicional:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="incluir_estadisticas" id="incluir_estadisticas" checked>
                            <label class="form-check-label" for="incluir_estadisticas">
                                Incluir estadísticas detalladas
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="incluir_graficos" id="incluir_graficos" checked>
                            <label class="form-check-label" for="incluir_graficos">
                                Incluir gráficos
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Generar Reporte PDF
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Previsualización del reporte -->
<div class="card fadeIn mt-4">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-eye"></i> Previsualización
        </div>
    </div>
    <div class="card-body">
        <div class="report-preview">
            <div class="report-header text-center">
                <h2>SENA - Control de Asistencia</h2>
                <h4 id="preview-title">Reporte Diario de Asistencias</h4>
                <p id="preview-date" class="text-muted">{{ now()->format('d/m/Y') }}</p>
            </div>

            <div class="report-content mt-4">
                <div id="report-filters" class="mb-4">
                    <h5>Filtros aplicados:</h5>
                    <ul class="list-unstyled">
                        <li><strong>Tipo de reporte:</strong> <span id="preview-tipo">Diario</span></li>
                        <li><strong>Período:</strong> <span id="preview-periodo">{{ now()->format('d/m/Y') }}</span></li>
                        <li><strong>Programa:</strong> <span id="preview-programa">Todos</span></li>
                        <li><strong>Jornada:</strong> <span id="preview-jornada">Todas</span></li>
                        <li><strong>Tipo de registro:</strong> <span id="preview-tipo-registro">Ambos</span></li>
                    </ul>
                </div>

                <div class="report-sample mt-4">
                    <h5>El reporte incluirá:</h5>
                    <ul>
                        <li>Lista completa de asistencias según los filtros seleccionados</li>
                        <li id="preview-estadisticas">Estadísticas detalladas: conteo por tipo, porcentaje de puntualidad, tendencias</li>
                        <li id="preview-graficos">Gráficos explicativos: asistencia por día, por programa, puntualidad</li>
                        <li>Información de cada aprendiz: documento, nombre, programa, ficha</li>
                        <li>Detalle de cada registro: fecha, hora, tipo, estado (a tiempo/tarde/salida anticipada)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-group.report-type-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.btn-group.report-type-selector .btn {
    flex: 1;
    min-width: 100px;
    margin-bottom: 5px;
}

.report-preview {
    padding: 20px;
    border: 1px dashed #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
}
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        // Mostrar/ocultar filtros según el tipo de reporte seleccionado
        $('input[name="tipo_reporte"]').change(function() {
            const tipoReporte = $(this).val();
            
            // Ocultar todos los filtros específicos
            $('.filtro-personalizado, .filtro-programa, .filtro-jornada, .filtro-aprendiz').hide();
            
            // Mostrar filtros según selección
            switch(tipoReporte) {
                case 'personalizado':
                    $('.filtro-personalizado').show();
                    break;
                case 'programa':
                    $('.filtro-programa').show();
                    break;
                case 'jornada':
                    $('.filtro-jornada').show();
                    break;
                case 'aprendiz':
                    $('.filtro-aprendiz').show();
                    break;
            }
            
            // Actualizar previsualización
            actualizarPrevisualizacion();
        });
        
        // Eventos para actualizar previsualización
        $('#tipo, #programa_id, #jornada_id, #fecha_inicio, #fecha_fin').change(actualizarPrevisualizacion);
        $('#incluir_estadisticas, #incluir_graficos').change(actualizarPrevisualizacion);
        
        // Función para buscar aprendices
        $('#btn-search-aprendiz').click(function() {
            const query = $('#aprendiz_search').val();
            if (query.length < 3) {
                alert('Ingrese al menos 3 caracteres para buscar');
                return;
            }
            
            $.ajax({
                url: '/admin/api/buscar-aprendices',
                data: { query: query },
                success: function(data) {
                    let html = '<div class="list-group">';
                    if (data.length === 0) {
                        html += '<div class="list-group-item">No se encontraron resultados</div>';
                    } else {
                        data.forEach(function(aprendiz) {
                            html += `<a href="#" class="list-group-item list-group-item-action select-aprendiz" 
                                      data-id="${aprendiz.id}" data-nombre="${aprendiz.nombres_completos}">
                                      ${aprendiz.nombres_completos} (${aprendiz.documento_identidad})
                                     </a>`;
                        });
                    }
                    html += '</div>';
                    $('#aprendices-results').html(html);
                }
            });
        });
        
        // Seleccionar aprendiz de la lista
        $(document).on('click', '.select-aprendiz', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            
            $('#aprendiz_id').val(id);
            $('#aprendiz_search').val(nombre);
            $('#aprendices-results').html('');
            
            actualizarPrevisualizacion();
        });
        
        // Función para actualizar la previsualización
        function actualizarPrevisualizacion() {
            const tipoReporte = $('input[name="tipo_reporte"]:checked').val();
            let titulo = 'Reporte de Asistencias';
            let periodo = '';
            
            switch(tipoReporte) {
                case 'diario':
                    titulo = 'Reporte Diario de Asistencias';
                    periodo = moment().format('DD/MM/YYYY');
                    break;
                case 'semanal':
                    titulo = 'Reporte Semanal de Asistencias';
                    periodo = `${moment().startOf('week').format('DD/MM/YYYY')} al ${moment().endOf('week').format('DD/MM/YYYY')}`;
                    break;
                case 'mensual':
                    titulo = 'Reporte Mensual de Asistencias';
                    periodo = moment().format('MMMM YYYY');
                    break;
                case 'personalizado':
                    titulo = 'Reporte Personalizado de Asistencias';
                    const fechaInicio = $('#fecha_inicio').val();
                    const fechaFin = $('#fecha_fin').val();
                    if (fechaInicio && fechaFin) {
                        periodo = `${moment(fechaInicio).format('DD/MM/YYYY')} al ${moment(fechaFin).format('DD/MM/YYYY')}`;
                    } else {
                        periodo = 'Período no especificado';
                    }
                    break;
                case 'programa':
                    titulo = 'Reporte por Programa de Formación';
                    const programaText = $('#programa_id option:selected').text();
                    $('#preview-programa').text(programaText || 'Todos');
                    periodo = 'Últimos 30 días';
                    break;
                case 'jornada':
                    titulo = 'Reporte por Jornada';
                    const jornadaText = $('#jornada_id option:selected').text();
                    $('#preview-jornada').text(jornadaText || 'Todas');
                    periodo = 'Últimos 30 días';
                    break;
                case 'aprendiz':
                    titulo = 'Reporte por Aprendiz';
                    periodo = 'Últimos 30 días';
                    break;
            }
            
            // Actualizar elementos de previsualización
            $('#preview-title').text(titulo);
            $('#preview-tipo').text(tipoReporte.charAt(0).toUpperCase() + tipoReporte.slice(1));
            $('#preview-periodo').text(periodo);
            
            // Tipo de registro
            const tipoRegistro = $('#tipo option:selected').text();
            $('#preview-tipo-registro').text(tipoRegistro);
            
            // Estadísticas y gráficos
            if (!$('#incluir_estadisticas').is(':checked')) {
                $('#preview-estadisticas').hide();
            } else {
                $('#preview-estadisticas').show();
            }
            
            if (!$('#incluir_graficos').is(':checked')) {
                $('#preview-graficos').hide();
            } else {
                $('#preview-graficos').show();
            }
        }
        
        // Inicializar previsualización
        actualizarPrevisualizacion();
    });
</script>
@endpush
@endsection 