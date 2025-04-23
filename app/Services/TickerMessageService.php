<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TickerMessageService
{
    private $cacheExpiration = 1800; // 30 minutos

    /**
     * Obtiene las estadísticas de portátiles por jornada del día actual
     */
    public function getPortatilesPorJornada(): array
    {
        $today = Carbon::today();
        return DB::table('asistencias as a')
            ->join('users as u', 'a.user_id', '=', 'u.id')
            ->join('devices as d', 'd.user_id', '=', 'u.id')
            ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
            ->select('j.nombre as jornada', DB::raw('COUNT(DISTINCT u.id) as total_portatiles'))
            ->where('a.fecha_hora', '>=', $today)
            ->where('a.fecha_hora', '<', $today->copy()->addDay())
            ->groupBy('j.nombre')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene el primer aprendiz en llegar por jornada
     */
    public function getPrimerosEnLlegar(): array
    {
        $today = Carbon::today();
        return DB::table('asistencias as a')
            ->join('users as u', 'a.user_id', '=', 'u.id')
            ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
            ->join('programa_formacion as p', 'u.id', '=', 'p.user_id')
            ->select(
                'j.nombre as jornada',
                'u.nombres_completos',
                'a.fecha_hora',
                'p.nombre_programa',
                'p.numero_ficha'
            )
            ->where('a.tipo', 'entrada')
            ->where('a.fecha_hora', '>=', $today)
            ->where('a.fecha_hora', '<', $today->copy()->addDay())
            ->whereRaw('(j.nombre, a.fecha_hora) IN (
                SELECT j2.nombre, MIN(a2.fecha_hora)
                FROM asistencias a2
                JOIN users u2 ON a2.user_id = u2.id
                JOIN jornadas j2 ON u2.jornada_id = j2.id
                WHERE a2.tipo = "entrada"
                AND a2.fecha_hora >= ?
                AND a2.fecha_hora < ?
                GROUP BY j2.nombre
            )', [$today, $today->copy()->addDay()])
            ->get()
            ->toArray();
    }

    /**
     * Obtiene estadísticas de portátiles por marca
     */
    public function getPortatilesPorMarca(): array
    {
        return Cache::remember('portatiles_por_marca', $this->cacheExpiration, function () {
            return DB::table('devices')
                ->select('marca', DB::raw('COUNT(*) as total'))
                ->groupBy('marca')
                ->having('total', '>', 1)
                ->orderBy('total', 'desc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Obtiene datos de los programas de formación
     */
    public function getDatosProgramas(): array
    {
        return Cache::remember('datos_programas', $this->cacheExpiration, function () {
            return DB::table('programa_formacion')
                ->select('nombre_programa', 'numero_ficha', 'numero_ambiente')
                ->get()
                ->toArray();
        });
    }

    /**
     * Obtiene los nuevos aprendices registrados en las últimas 24 horas
     */
    public function getNuevosAprendices(): array
    {
        return Cache::remember('nuevos_aprendices', 300, function () { // 5 minutos
            return DB::table('users as u')
                ->join('programa_formacion as p', 'u.id', '=', 'p.user_id')
                ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
                ->select(
                    'u.nombres_completos',
                    'p.nombre_programa',
                    'p.numero_ficha',
                    'j.nombre as jornada',
                    'u.created_at'
                )
                ->where('u.rol', 'aprendiz')
                ->where('u.created_at', '>=', now()->subDay())
                ->orderBy('u.created_at', 'desc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Obtiene las últimas asistencias registradas
     */
    public function getUltimasAsistencias(int $minutos = 15): array
    {
        return Cache::remember('ultimas_asistencias', 60, function () use ($minutos) { // 1 minuto
            return DB::table('asistencias as a')
                ->join('users as u', 'a.user_id', '=', 'u.id')
                ->join('programa_formacion as p', 'u.id', '=', 'p.user_id')
                ->join('jornadas as j', 'u.jornada_id', '=', 'j.id')
                ->where('a.fecha_hora', '>=', Carbon::now()->subMinutes($minutos))
                ->select(
                    'u.nombres_completos',
                    'p.nombre_programa',
                    'p.numero_ficha',
                    'j.nombre as jornada',
                    'a.fecha_hora',
                    'a.tipo'
                )
                ->orderBy('a.fecha_hora', 'desc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Genera mensajes personalizados para nuevos aprendices
     */
    private function generarMensajesNuevosAprendices(array $aprendices): array
    {
        $mensajes = [];
        foreach ($aprendices as $aprendiz) {
            $mensajes[] = sprintf(
                "👋 ¡Bienvenido al SENA %s! Te has unido al programa %s (Ficha %s) en la jornada %s",
                $aprendiz->nombres_completos,
                $aprendiz->nombre_programa,
                $aprendiz->numero_ficha,
                $aprendiz->jornada
            );
        }
        return $mensajes;
    }

    /**
     * Genera mensajes personalizados para asistencias
     */
    private function generarMensajesAsistencias(array $asistencias): array
    {
        $mensajes = [];
        foreach ($asistencias as $asistencia) {
            $horaFormateada = Carbon::parse($asistencia->fecha_hora)->format('h:i A');
            
            if ($asistencia->tipo === 'entrada') {
                $mensajes[] = sprintf(
                    "✅ %s de la ficha %s ha llegado a las %s",
                    $asistencia->nombres_completos,
                    $asistencia->numero_ficha,
                    $horaFormateada
                );
            } else {
                $mensajes[] = sprintf(
                    "👋 %s de la ficha %s se ha retirado a las %s",
                    $asistencia->nombres_completos,
                    $asistencia->numero_ficha,
                    $horaFormateada
                );
            }
        }
        return $mensajes;
    }

    /**
     * Genera todos los mensajes para el ticker
     */
    public function getMensajes(): array
    {
        $mensajes = [
            "👋 ¡Bienvenidos al SENA!",
            "💻 Sistema de Control de Asistencia"
        ];
        
        // Intentar obtener cada tipo de mensaje independientemente
        try {
            $portatilesPorJornada = $this->getPortatilesPorJornada();
            foreach ($portatilesPorJornada as $dato) {
                $mensajes[] = sprintf(
                    "📱 La jornada %s cuenta con %d portátiles registrados", 
                    $dato->jornada, 
                    $dato->total_portatiles
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo portátiles por jornada:', ['error' => $e->getMessage()]);
        }

        try {
            $primerosEnLlegar = $this->getPrimerosEnLlegar();
            foreach ($primerosEnLlegar as $primero) {
                $hora = Carbon::parse($primero->fecha_hora)->format('h:i A');
                $mensajes[] = sprintf(
                    "🥇 %s del programa %s llegó primero en la jornada %s a las %s", 
                    $primero->nombres_completos,
                    $primero->nombre_programa,
                    $primero->jornada,
                    $hora
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo primeros en llegar:', ['error' => $e->getMessage()]);
        }

        try {
            $portatilesPorMarca = $this->getPortatilesPorMarca();
            foreach ($portatilesPorMarca as $marca) {
                $mensajes[] = sprintf(
                    "💻 %d aprendices utilizan equipos %s", 
                    $marca->total,
                    $marca->marca
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo portátiles por marca:', ['error' => $e->getMessage()]);
        }

        try {
            $programas = $this->getDatosProgramas();
            foreach ($programas as $programa) {
                $mensajes[] = sprintf(
                    "📚 Programa %s - Ficha %s - Ambiente %s", 
                    $programa->nombre_programa,
                    $programa->numero_ficha,
                    $programa->numero_ambiente
                );
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo datos de programas:', ['error' => $e->getMessage()]);
        }

        try {
            $nuevosAprendices = $this->getNuevosAprendices();
            $mensajesNuevos = $this->generarMensajesNuevosAprendices($nuevosAprendices);
            $mensajes = array_merge($mensajes, $mensajesNuevos);
        } catch (\Exception $e) {
            Log::error('Error obteniendo nuevos aprendices:', ['error' => $e->getMessage()]);
        }

        try {
            $ultimasAsistencias = $this->getUltimasAsistencias(30);
            $mensajesAsistencias = $this->generarMensajesAsistencias($ultimasAsistencias);
            $mensajes = array_merge($mensajes, $mensajesAsistencias);
        } catch (\Exception $e) {
            Log::error('Error obteniendo últimas asistencias:', ['error' => $e->getMessage()]);
        }

        // Limpiar mensajes vacíos y duplicados
        return array_values(array_unique(array_filter($mensajes)));
    }
}