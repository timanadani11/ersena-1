<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PuntualidadService
{
    /**
     * Calcular puntuación de puntualidad basada en asistencias
     */
    public function calcularPuntuaciones($jornada_id = null, $fecha_inicio = null, $fecha_fin = null)
    {
        if (!$fecha_inicio) {
            $fecha_inicio = Carbon::now()->subDays(30)->startOfDay();
        }
        
        if (!$fecha_fin) {
            $fecha_fin = Carbon::now()->endOfDay();
        }
        
        $query = User::select(
            'users.id',
            'users.nombres_completos',
            'pf.nivel_formacion',
            'j.nombre as jornada',
            'j.hora_entrada',
            'pf.nombre_programa',
            'pf.numero_ficha'
        )
        ->join('programa_formacion as pf', 'users.id', '=', 'pf.user_id')
        ->join('jornadas as j', 'pf.jornada_id', '=', 'j.id')
        ->where('users.rol', 'aprendiz');
        
        if ($jornada_id) {
            $query->where('pf.jornada_id', $jornada_id);
        }
        
        $usuarios = $query->get();
        $resultados = [];
        
        foreach ($usuarios as $usuario) {
            $asistencias = Asistencia::where('user_id', $usuario->id)
                ->where('tipo', 'entrada')
                ->whereBetween('fecha_hora', [$fecha_inicio, $fecha_fin])
                ->get();
            
            if ($asistencias->count() == 0) continue;
            
            $totalPuntos = 0;
            $totalAsistencias = $asistencias->count();
            $llegadasPuntuales = 0;
            $tiempoPromedio = 0;
            
            foreach ($asistencias as $asistencia) {
                $fechaAsistencia = Carbon::parse($asistencia->fecha_hora);
                $horaEntradaEsperada = Carbon::parse($usuario->hora_entrada);
                $horaEntradaEsperada->setDateFrom($fechaAsistencia);
                
                $diferencia = $fechaAsistencia->diffInMinutes($horaEntradaEsperada, false);
                $tiempoPromedio += $diferencia;
                
                if ($diferencia <= -5) {
                    $puntos = 10;
                    $llegadasPuntuales++;
                } elseif ($diferencia <= 0) {
                    $puntos = 8;
                    $llegadasPuntuales++;
                } elseif ($diferencia <= 5) {
                    $puntos = 5;
                    $llegadasPuntuales++;
                } elseif ($diferencia <= 15) {
                    $puntos = 3;
                } else {
                    $puntos = 0;
                }
                
                $totalPuntos += $puntos;
            }
            
            $puntuacionPromedio = $totalAsistencias > 0 ? round($totalPuntos / $totalAsistencias, 1) : 0;
            $porcentajePuntualidad = $totalAsistencias > 0 ? round(($llegadasPuntuales / $totalAsistencias) * 100) : 0;
            $tiempoPromedioMinutos = $totalAsistencias > 0 ? round($tiempoPromedio / $totalAsistencias) : 0;
            
            $resultados[] = [
                'id' => $usuario->id,
                'nombre' => $usuario->nombres_completos,
                'jornada' => $usuario->jornada,
                'programa' => $usuario->nombre_programa,
                'ficha' => $usuario->numero_ficha,
                'nivel_formacion' => $usuario->nivel_formacion,
                'puntuacion' => $puntuacionPromedio,
                'total_puntos' => $totalPuntos,
                'asistencias' => $totalAsistencias,
                'puntualidad' => $porcentajePuntualidad,
                'tiempo_promedio' => $tiempoPromedioMinutos,
            ];
        }
        
        usort($resultados, function($a, $b) {
            return $b['total_puntos'] - $a['total_puntos'];
        });
        
        return array_slice($resultados, 0, 10);
    }
}