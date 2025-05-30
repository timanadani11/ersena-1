<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 100%;
            padding: 10px;
        }
        
        /* Encabezado */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
        
        .title {
            font-size: 18pt;
            font-weight: bold;
            margin: 5px 0;
            color: #006064;
        }
        
        .subtitle {
            font-size: 12pt;
            margin: 5px 0;
            color: #00838f;
        }
        
        .period {
            font-size: 10pt;
            color: #555;
        }
        
        /* Sección de filtros */
        .filters {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        
        .filters-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #444;
        }
        
        .filter-item {
            display: inline-block;
            margin-right: 15px;
        }
        
        .filter-label {
            font-weight: bold;
        }
        
        /* Estadísticas */
        .stats {
            margin-bottom: 20px;
        }
        
        .stats-row {
            display: flex;
            margin-bottom: 15px;
        }
        
        .stat-box {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            margin-right: 10px;
            text-align: center;
            background-color: #f5f5f5;
        }
        
        .stat-box:last-child {
            margin-right: 0;
        }
        
        .stat-value {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
            color: #0277bd;
        }
        
        .stat-label {
            font-size: 8pt;
            color: #555;
        }
        
        /* Tablas */
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-align: left;
            padding: 6px;
        }
        
        td {
            padding: 5px;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-entrada {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .badge-salida {
            background-color: #fff8e1;
            color: #ff8f00;
        }
        
        .badge-a-tiempo {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-tarde {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .badge-anticipada {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        /* Gráficos */
        .chart-container {
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 10pt;
        }
        
        /* Pie de página */
        .footer {
            font-size: 8pt;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            margin-top: 20px;
        }
        
        /* Columnas */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -5px;
            margin-left: -5px;
        }
        
        .col {
            flex: 1;
            padding: 0 5px;
        }
        
        .col-6 {
            width: 50%;
            padding: 0 5px;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            @if(extension_loaded('gd'))
            <img src="{{ public_path('img/logo/logoSena.png') }}" alt="SENA" class="logo">
            @else
            <div style="text-align: center; margin-bottom: 10px; font-size: 18pt; font-weight: bold; color: #006064;">SENA</div>
            @endif
            <h1 class="title">SENA - Control de Asistencia</h1>
            <h2 class="subtitle">{{ $titulo }}</h2>
            <p class="period">Período: {{ $periodo }}</p>
        </div>
        
        <!-- Filtros aplicados -->
        <div class="filters">
            <div class="filters-title">Filtros aplicados:</div>
            <div class="filter-item">
                <span class="filter-label">Tipo de reporte:</span> {{ ucfirst($request->tipo_reporte) }}
            </div>
            <div class="filter-item">
                <span class="filter-label">Programa:</span> {{ $programaNombre }}
            </div>
            <div class="filter-item">
                <span class="filter-label">Jornada:</span> {{ $jornadaNombre }}
            </div>
            @if($request->aprendiz_id)
            <div class="filter-item">
                <span class="filter-label">Aprendiz:</span> {{ $aprendizNombre }}
            </div>
            @endif
            <div class="filter-item">
                <span class="filter-label">Tipo de registro:</span> 
                {{ $request->tipo ? ($request->tipo == 'entrada' ? 'Solo entradas' : 'Solo salidas') : 'Ambos tipos' }}
            </div>
        </div>
        
        <!-- Estadísticas si están habilitadas -->
        @if($request->has('incluir_estadisticas') && $estadisticas)
        <div class="stats">
            <h3>Estadísticas Generales</h3>
            
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value">{{ $estadisticas['total'] }}</div>
                    <div class="stat-label">Total de Registros</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $estadisticas['entradas'] }}</div>
                    <div class="stat-label">Entradas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $estadisticas['salidas'] }}</div>
                    <div class="stat-label">Salidas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $estadisticas['a_tiempo'] }}</div>
                    <div class="stat-label">A Tiempo</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $estadisticas['fuera_de_horario'] }}</div>
                    <div class="stat-label">Fuera de Horario</div>
                </div>
            </div>
            
            <!-- Tabla de estadísticas por programa -->
            @if(count($estadisticas['por_programa']) > 0)
            <h4>Distribución por Programa</h4>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Programa</th>
                            <th>Total</th>
                            <th>Entradas</th>
                            <th>Salidas</th>
                            <th>% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estadisticas['por_programa'] as $programa => $stats)
                        <tr>
                            <td>{{ $programa }}</td>
                            <td>{{ $stats['total'] }}</td>
                            <td>{{ $stats['entradas'] }}</td>
                            <td>{{ $stats['salidas'] }}</td>
                            <td>{{ number_format(($stats['total'] / $estadisticas['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Tabla de estadísticas por jornada -->
            @if(count($estadisticas['por_jornada']) > 0)
            <h4>Distribución por Jornada</h4>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Jornada</th>
                            <th>Total</th>
                            <th>Entradas</th>
                            <th>Salidas</th>
                            <th>% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estadisticas['por_jornada'] as $jornada => $stats)
                        <tr>
                            <td>{{ $jornada }}</td>
                            <td>{{ $stats['total'] }}</td>
                            <td>{{ $stats['entradas'] }}</td>
                            <td>{{ $stats['salidas'] }}</td>
                            <td>{{ number_format(($stats['total'] / $estadisticas['total']) * 100, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Tabla de estadísticas por fecha -->
            @if(count($estadisticas['por_fecha']) > 0)
            <h4>Distribución por Fecha</h4>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Entradas</th>
                            <th>Salidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estadisticas['por_fecha'] as $fecha => $stats)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
                            <td>{{ $stats['total'] }}</td>
                            <td>{{ $stats['entradas'] }}</td>
                            <td>{{ $stats['salidas'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Tabla de asistencias -->
        <h3>Listado de Asistencias</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Aprendiz</th>
                        <th>Programa</th>
                        <th>Ficha</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        @if($estadisticas && $estadisticas['fuera_de_horario'] > 0)
                        <th>Observaciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($asistencias as $asistencia)
                    <tr>
                        <td>{{ $asistencia->user->documento_identidad }}</td>
                        <td>{{ $asistencia->user->nombres_completos }}</td>
                        <td>{{ $asistencia->user->programaFormacion->nombre_programa ?? 'N/A' }}</td>
                        <td>{{ $asistencia->user->programaFormacion->numero_ficha ?? 'N/A' }}</td>
                        <td>{{ $asistencia->fecha_hora->format('d/m/Y') }}</td>
                        <td>{{ $asistencia->fecha_hora->format('H:i:s') }}</td>
                        <td>
                            <span class="badge badge-{{ $asistencia->tipo }}">
                                {{ $asistencia->tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                            </span>
                        </td>
                        <td>
                            @if($asistencia->fuera_de_horario)
                                <span class="badge badge-tarde">Fuera de horario</span>
                            @elseif($asistencia->salida_anticipada)
                                <span class="badge badge-anticipada">Salida anticipada</span>
                            @else
                                <span class="badge badge-a-tiempo">A tiempo</span>
                            @endif
                        </td>
                        @if($estadisticas && $estadisticas['fuera_de_horario'] > 0)
                        <td>{{ $asistencia->observaciones ?? '' }}</td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $estadisticas && $estadisticas['fuera_de_horario'] > 0 ? 9 : 8 }}" style="text-align: center;">
                            No se encontraron registros
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pie de página -->
        <div class="footer">
            <p>
                Documento generado el {{ now()->format('d/m/Y H:i:s') }} | 
                SENA - Control de Asistencia | 
                Total de registros: {{ $asistencias->count() }}
            </p>
        </div>
    </div>
</body>
</html> 