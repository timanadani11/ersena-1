<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .fecha-generacion {
            text-align: right;
            margin-bottom: 20px;
            font-style: italic;
            font-size: 10px;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
            display: inline-block;
        }
        .badge-success {
            background-color: #10b981;
        }
        .badge-danger {
            background-color: #ef4444;
        }
        .badge-info {
            background-color: #3b82f6;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h2>SENA - Control de Asistencia</h2>
    </div>

    <div class="fecha-generacion">
        Generado: {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <h1>Reporte de Asistencias</h1>

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
                        Entrada
                    @else
                        Salida
                    @endif
                </td>
                <td>
                    @php
                        $estado = 'A tiempo';
                        if ($asistencia->tipo === 'entrada' && $asistencia->fuera_de_horario) {
                            $estado = 'Tarde';
                        } elseif ($asistencia->tipo === 'salida' && $asistencia->salida_anticipada) {
                            $estado = 'Salida anticipada';
                        }
                    @endphp
                    {{ $estado }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">No hay registros de asistencia</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este es un documento generado automáticamente por el sistema de control de asistencia SENA.</p>
    </div>
</body>
</html> 