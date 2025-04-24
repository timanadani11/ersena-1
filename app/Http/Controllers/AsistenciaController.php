<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    public function getAsistenciasDiarias()
    {
        try {
            $fecha = Carbon::today('America/Bogota');
            
            $asistencias = Asistencia::with([
                'user' => function($query) {
                    $query->select('id', 'nombres_completos', 'documento_identidad')
                        ->with([
                            'programaFormacion' => function($q) {
                                $q->select('id', 'user_id', 'nombre_programa', 'numero_ficha', 'numero_ambiente', 'nivel_formacion', 'jornada_id')
                                  ->with(['jornada:id,nombre,hora_entrada,tolerancia']);
                            },
                            'devices' => function($q) {
                                $q->select('id', 'user_id', 'marca', 'serial')
                                  ->latest()
                                  ->take(1);
                            }
                        ]);
                }
            ])
            ->whereDate('fecha_hora', $fecha)
            ->orderBy('fecha_hora', 'desc')
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $asistencias->map(function ($asistencia) {
                    $user = $asistencia->user;
                    $programa = $user->programaFormacion;
                    $jornada = $programa ? $programa->jornada : null;

                    return [
                        'id' => $asistencia->id,
                        'user_id' => $asistencia->user_id,
                        'tipo' => $asistencia->tipo,
                        'fecha_hora' => $asistencia->fecha_hora->format('Y-m-d H:i:s'),
                        'user' => [
                            'id' => $user->id,
                            'nombres_completos' => $user->nombres_completos,
                            'documento_identidad' => $user->documento_identidad,
                            'programa_formacion' => $programa ? [
                                'nombre_programa' => $programa->nombre_programa,
                                'numero_ficha' => $programa->numero_ficha,
                                'numero_ambiente' => $programa->numero_ambiente,
                                'nivel_formacion' => $programa->nivel_formacion
                            ] : null,
                            'jornada' => $jornada ? [
                                'nombre' => $jornada->nombre,
                                'hora_entrada' => $jornada->hora_entrada,
                                'tolerancia' => $jornada->tolerancia
                            ] : null,
                            'devices' => $user->devices->map(function ($device) {
                                return [
                                    'marca' => $device->marca,
                                    'serial' => $device->serial
                                ];
                            })
                        ]
                    ];
                }),
                'meta' => [
                    'total' => $asistencias->count(),
                    'fecha' => $fecha->format('Y-m-d'),
                    'hora_actualizacion' => Carbon::now('America/Bogota')->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener asistencias diarias:', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las asistencias: ' . $e->getMessage()
            ], 500);
        }
    }
}