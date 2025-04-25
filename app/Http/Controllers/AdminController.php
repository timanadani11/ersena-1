<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
        $page = request()->get('page', 1);
        $perPage = 20;

        $asistencias = Asistencia::with(['user.programaFormacion', 'user.devices'])
            ->whereDate('fecha_hora', '>=', now()->setTimezone('America/Bogota')->subDays(30))
            ->orderBy('fecha_hora', 'desc')
            ->get();

        $grupos = $asistencias->groupBy(function($asistencia) {
            return $asistencia->user_id . '_' . $asistencia->fecha_hora->setTimezone('America/Bogota')->format('Y-m-d');
        })->map(function($grupo) {
            return $grupo->map(function($asistencia) {
                return $asistencia->toArray();
            });
        });

        $items = new Collection($grupos);
        $items = $items->values();

        $paginatedItems = new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return view('admin.dashboard', ['asistencias' => $paginatedItems]);
    }

    public function verificarAsistencia(Request $request)
    {
        $request->validate([
            'documento_identidad' => 'required|string',
        ]);

        $user = User::where('documento_identidad', $request->documento_identidad)
            ->where('rol', 'aprendiz')
            ->with([
                'programaFormacion',
                'jornada',
                'devices' => function($query) {
                    $query->latest()->first();
                }
            ])
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Aprendiz no encontrado'], 404);
        }

        // Obtener todas las asistencias del día para el usuario
        $asistenciasHoy = Asistencia::where('user_id', $user->id)
            ->whereDate('fecha_hora', now()->setTimezone('America/Bogota')->format('Y-m-d'))
            ->orderBy('fecha_hora', 'desc')
            ->get();

        // Contar entradas y salidas
        $entradasHoy = $asistenciasHoy->where('tipo', 'entrada')->count();
        $salidasHoy = $asistenciasHoy->where('tipo', 'salida')->count();

        // Si ya tiene entrada y salida, no puede registrar más
        if ($entradasHoy >= 1 && $salidasHoy >= 1) {
            return response()->json([
                'user' => $user,
                'puede_registrar_entrada' => false,
                'puede_registrar_salida' => false,
                'mensaje' => 'Ya completó los registros de entrada y salida para hoy',
                'asistencias_hoy' => $asistenciasHoy,
                'estadisticas' => [
                    'entradas_totales' => $entradasHoy,
                    'salidas_totales' => $salidasHoy,
                    'hora_jornada' => $user->jornada ? $user->jornada->hora_entrada : null,
                    'tolerancia' => $user->jornada ? $user->jornada->tolerancia : null
                ]
            ]);
        }

        // Solo puede registrar entrada si no tiene entradas hoy
        $puedeRegistrarEntrada = $entradasHoy === 0;
        
        // Solo puede registrar salida si tiene una entrada y no tiene salidas
        $puedeRegistrarSalida = $entradasHoy === 1 && $salidasHoy === 0;

        // Obtener la última asistencia registrada (histórico)
        $ultimaAsistencia = Asistencia::where('user_id', $user->id)
            ->orderBy('fecha_hora', 'desc')
            ->first();

        return response()->json([
            'user' => $user,
            'puede_registrar_entrada' => $puedeRegistrarEntrada,
            'puede_registrar_salida' => $puedeRegistrarSalida,
            'asistencias_hoy' => $asistenciasHoy,
            'ultima_asistencia' => $ultimaAsistencia,
            'estadisticas' => [
                'entradas_totales' => $entradasHoy,
                'salidas_totales' => $salidasHoy,
                'hora_jornada' => $user->jornada ? $user->jornada->hora_entrada : null,
                'tolerancia' => $user->jornada ? $user->jornada->tolerancia : null
            ]
        ]);
    }

    public function buscarPorQR(Request $request)
    {
        try {
            $request->validate([
                'qr_code' => 'required|string',
            ]);

            $codigo = trim($request->qr_code);
            Log::info('Código recibido en buscarPorQR: ' . $codigo);

            // Primero intenta buscar por QR code (para QRs generados por el sistema)
            $user = User::where('qr_code', $codigo)
                ->where('rol', 'aprendiz')
                ->with(['programaFormacion', 'devices', 'jornada'])
                ->first();

            Log::info('Búsqueda por QR: ' . ($user ? 'Usuario encontrado' : 'Usuario no encontrado'));

            // Si no encuentra por QR, intenta buscar por documento de identidad
            if (!$user) {
                // Limpiamos el código para asegurarnos que solo contiene números
                $documento = preg_replace('/[^0-9]/', '', $codigo);
                Log::info('Intentando con documento limpio: ' . $documento);
                
                if (!empty($documento)) {
                    $user = User::where('documento_identidad', $documento)
                        ->where('rol', 'aprendiz')
                        ->with(['programaFormacion', 'devices', 'jornada'])
                        ->first();
                    
                    Log::info('Búsqueda por documento: ' . ($user ? 'Usuario encontrado' : 'Usuario no encontrado'));
                } else {
                    Log::warning('El código no contiene números: ' . $codigo);
                    return response()->json([
                        'error' => 'El código no contiene un documento válido',
                        'codigo_recibido' => $codigo
                    ], 404);
                }
            }

            if (!$user) {
                Log::warning('No se encontró usuario para el código/documento: ' . $codigo);
                return response()->json([
                    'error' => 'Aprendiz no encontrado',
                    'codigo_recibido' => $codigo,
                    'documento_limpio' => $documento ?? null
                ], 404);
            }

            Log::info('Usuario encontrado exitosamente: ' . $user->nombres_completos);
            return $this->verificarAsistencia(new Request(['documento_identidad' => $user->documento_identidad]));

        } catch (\Exception $e) {
            Log::error('Error en buscarPorQR: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al procesar el código',
                'mensaje' => $e->getMessage(),
                'codigo_recibido' => $request->qr_code ?? null
            ], 500);
        }
    }

    public function registrarAsistencia(Request $request)
    {
        $request->validate([
            'documento_identidad' => 'required|string',
            'tipo' => 'required|in:entrada,salida',
        ]);

        $user = User::where('documento_identidad', $request->documento_identidad)
            ->where('rol', 'aprendiz')
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Aprendiz no encontrado'], 404);
        }

        // Obtener asistencias del día
        $asistenciasHoy = Asistencia::where('user_id', $user->id)
            ->whereDate('fecha_hora', now()->setTimezone('America/Bogota')->format('Y-m-d'))
            ->get();

        $entradasHoy = $asistenciasHoy->where('tipo', 'entrada')->count();
        $salidasHoy = $asistenciasHoy->where('tipo', 'salida')->count();

        // Validaciones específicas según el tipo de registro
        if ($request->tipo === 'entrada') {
            if ($entradasHoy >= 1) {
                return response()->json([
                    'error' => 'Ya registró su entrada para el día de hoy'
                ], 400);
            }
        } else { // tipo === 'salida'
            if ($entradasHoy === 0) {
                return response()->json([
                    'error' => 'Debe registrar una entrada antes de registrar una salida'
                ], 400);
            }
            if ($salidasHoy >= 1) {
                return response()->json([
                    'error' => 'Ya registró su salida para el día de hoy'
                ], 400);
            }
        }

        try {
            $asistencia = Asistencia::create([
                'user_id' => $user->id,
                'tipo' => $request->tipo,
                'fecha_hora' => now()->setTimezone('America/Bogota'),
                'registrado_por' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Asistencia registrada correctamente',
                'asistencia' => $asistencia
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar asistencia: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al registrar la asistencia'
            ], 500);
        }
    }
}
