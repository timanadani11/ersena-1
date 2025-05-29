<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #10b981;
        }
        .logo {
            margin-bottom: 10px;
        }
        h1 {
            font-size: 18px;
            margin: 5px 0;
            color: #10b981;
        }
        h2 {
            font-size: 16px;
            margin: 15px 0 10px;
            color: #10b981;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 14px;
            margin: 10px 0 5px;
        }
        .date {
            font-style: italic;
            color: #666;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 6px;
            text-align: left;
        }
        td {
            border: 1px solid #dee2e6;
            padding: 6px;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .filter-item {
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #10b981;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 9px;
            border-radius: 3px;
            color: white;
        }
        .badge-success { background-color: #10b981; }
        .badge-danger { background-color: #ef4444; }
        .badge-warning { background-color: #f59e0b; }
        .badge-info { background-color: #3b82f6; }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <strong>SENA</strong> - Sistema de Control de Asistencia
            </div>
            <h1>{{ $titulo }}</h1>
            <div class="date">
                Generado: {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>

        <!-- Resumen de filtros aplicados -->
        <div class="summary-box">
            <h3>Filtros aplicados</h3>
            <div class="filters">
                @if($filtros['fecha_inicio'])
                    <div class="filter-item">
                        <strong>Desde:</strong> {{ $filtros['fecha_inicio']->format('d/m/Y') }}
                    </div>
                @endif
                
                @if($filtros['fecha_fin'])
                    <div class="filter-item">
                        <strong>Hasta:</strong> {{ $filtros['fecha_fin']->format('d/m/Y') }}
                    </div>
                @endif
                
                @if($filtros['programa'])
                    <div class="filter-item">
                        <strong>Programa:</strong> {{ $filtros['programa']->nombre_programa }}
                    </div>
                @endif
                
                @if($filtros['jornada'])
                    <div class="filter-item">
                        <strong>Jornada:</strong> {{ $filtros['jornada']->nombre }}
                    </div>
                @endif
                
                @if($filtros['aprendiz'])
                    <div class="filter-item">
                        <strong>Aprendiz:</strong> {{ $filtros['aprendiz']->nombres_completos }}
                    </div>
                @endif
                
                @if($filtros['tipo'])
                    <div class="filter-item">
                        <strong>Tipo:</strong> {{ $filtros['tipo'] === 'entrada' ? 'Entrada' : 'Salida' }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Estadísticas generales -->
        <h2>Estadísticas Generales</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value">{{ $estadisticas['total_asistencias'] }}</div>
                <div class="stat-label">Total registros</div>
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
                <div class="stat-value">{{ $estadisticas['llegadas_tarde'] }}</div>
                <div class="stat-label">Llegadas tarde</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $estadisticas['salidas_anticipadas'] }}</div>
                <div class="stat-label">Salidas anticipadas</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $estadisticas['porcentaje_puntualidad'] }}%</div>
                <div class="stat-label">Puntualidad</div>
            </div>
        </div>

        <!-- Asistencias por programa -->
        @if(count($estadisticas['asistencias_por_programa']) > 0)
        <h2>Asistencias por Programa</h2>
        <table>
            <thead>
                <tr>
                    <th>Programa</th>
                    <th>Total</th>
                    <th>Entradas</th>
                    <th>Salidas</th>
                    <th>Llegadas Tarde</th>
                    <th>% Puntualidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estadisticas['asistencias_por_programa'] as $programa => $datos)
                <tr>
                    <td>{{ $programa }}</td>
                    <td>{{ $datos['total'] }}</td>
                    <td>{{ $datos['entradas'] }}</td>
                    <td>{{ $datos['salidas'] }}</td>
                    <td>{{ $datos['llegadas_tarde'] }}</td>
                    <td>
                        @php 
                            $puntualidad = $datos['entradas'] > 0 
                                ? round(100 - (($datos['llegadas_tarde'] / $datos['entradas']) * 100), 2) 
                                : 100;
                        @endphp
                        {{ $puntualidad }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Asistencias por día de la semana -->
        @if(count($estadisticas['asistencias_por_dia']) > 0)
        <h2>Asistencias por Día de la Semana</h2>
        <table>
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Total</th>
                    <th>Entradas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estadisticas['asistencias_por_dia'] as $dia => $datos)
                <tr>
                    <td>{{ $dia }}</td>
                    <td>{{ $datos['total'] }}</td>
                    <td>{{ $datos['entradas'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Listado de asistencias -->
        <div class="page-break"></div>
        <h2>Listado de Asistencias</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Documento</th>
                    <th>Aprendiz</th>
                    <th>Programa</th>
                    <th>Ficha</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asistencias as $asistencia)
                <tr>
                    <td>{{ $asistencia->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td>{{ $asistencia->user->documento_identidad ?? 'N/A' }}</td>
                    <td>{{ $asistencia->user->nombres_completos ?? 'N/A' }}</td>
                    <td>{{ $asistencia->user->programaFormacion->nombre_programa ?? 'N/A' }}</td>
                    <td>{{ $asistencia->user->programaFormacion->numero_ficha ?? 'N/A' }}</td>
                    <td>
                        @if($asistencia->tipo === 'entrada')
                            <span class="badge badge-success">Entrada</span>
                        @else
                            <span class="badge badge-info">Salida</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $estado = 'A tiempo';
                            $badgeClass = 'badge-success';
                            
                            if ($asistencia->tipo === 'entrada' && $asistencia->fuera_de_horario) {
                                $estado = 'Tarde';
                                $badgeClass = 'badge-danger';
                            } elseif ($asistencia->tipo === 'salida' && $asistencia->salida_anticipada) {
                                $estado = 'Anticipada';
                                $badgeClass = 'badge-warning';
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $estado }}</span>
                    </td>
                    <td>{{ $asistencia->observaciones }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No hay registros de asistencia que coincidan con los filtros seleccionados</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>Este es un documento generado automáticamente por el sistema de control de asistencia SENA.</p>
            <p>Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i:s') }}</p>
        </div>
    </div>
</body>
</html> 