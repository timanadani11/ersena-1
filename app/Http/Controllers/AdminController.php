<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asistencia;
use App\Models\ProgramaFormacion;
use App\Models\Jornada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsistenciasExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Muestra la página principal del dashboard administrativo
     */
    public function dashboard()
    {
        // Estadísticas generales
        $estadisticas = $this->obtenerEstadisticasGenerales();
        
        return view('admin.dashboard.index', [
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Muestra la página del escáner QR
     */
    public function scanner() 
    {
        return view('admin.scanner.index');
    }

    /**
     * Muestra la página de gestión de aprendices
     */
    public function aprendices()
    {
        $aprendices = User::where('rol', 'aprendiz')
            ->with(['programaFormacion', 'jornada'])
            ->latest()
            ->paginate(15);
        
        // Obtener programas de formación para filtrado
        $programas = ProgramaFormacion::orderBy('nombre_programa')->get();
        
        // Obtener jornadas para filtrado
        $jornadas = Jornada::orderBy('nombre')->get();
        
        return view('admin.aprendices.index', [
            'aprendices' => $aprendices,
            'programas' => $programas,
            'jornadas' => $jornadas
        ]);
    }

    /**
     * Muestra la página de gestión de programas de formación
     */
    public function programas()
    {
        $programas = ProgramaFormacion::with('user')
            ->latest()
            ->paginate(15);
        
        return view('admin.programas.index', [
            'programas' => $programas
        ]);
    }

    /**
     * Muestra la página de reportes de asistencia
     */
    public function reportes()
    {
        $programas = ProgramaFormacion::all();
        $jornadas = Jornada::all();
        
        return view('admin.reportes.index', [
            'programas' => $programas,
            'jornadas' => $jornadas
        ]);
    }

    /**
     * Muestra la página de configuración
     */
    public function configuracion()
    {
        return view('admin.config.index');
    }

    /**
     * Muestra todas las asistencias
     */
    public function asistencias()
    {
        // Preparar filtros desde la solicitud
        $filtros = request()->only(['fecha_inicio', 'fecha_fin', 'programa_id', 'jornada_id', 'tipo', 'search']);
        
        // Construir la consulta con los filtros
        $query = Asistencia::with(['user.programaFormacion', 'user.jornada']);
        
        // Filtro por fecha de inicio
        if (!empty($filtros['fecha_inicio'])) {
            $query->whereDate('fecha_hora', '>=', $filtros['fecha_inicio']);
        }
        
        // Filtro por fecha de fin
        if (!empty($filtros['fecha_fin'])) {
            $query->whereDate('fecha_hora', '<=', $filtros['fecha_fin']);
        }
        
        // Filtro por programa
        if (!empty($filtros['programa_id'])) {
            $query->whereHas('user.programaFormacion', function($q) use ($filtros) {
                $q->where('id', $filtros['programa_id']);
            });
        }
        
        // Filtro por jornada
        if (!empty($filtros['jornada_id'])) {
            $query->whereHas('user', function($q) use ($filtros) {
                $q->where('jornada_id', $filtros['jornada_id']);
            });
        }
        
        // Filtro por tipo (entrada/salida)
        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }
        
        // Búsqueda por nombre o documento
        if (!empty($filtros['search'])) {
            $search = $filtros['search'];
            $query->whereHas('user', function($q) use ($search) {
                $q->where('nombres_completos', 'like', "%$search%")
                  ->orWhere('documento_identidad', 'like', "%$search%");
            });
        }
        
        // Ordenar por fecha descendente
        $query->latest('fecha_hora');
        
        // Paginar resultados
        $asistencias = $query->paginate(20)->withQueryString();
        
        // Obtener programas y jornadas para los filtros
        $programas = ProgramaFormacion::all();
        $jornadas = Jornada::all();
        
        return view('admin.asistencias.index', [
            'asistencias' => $asistencias,
            'programas' => $programas,
            'jornadas' => $jornadas,
            'filtros' => $filtros
        ]);
    }

    /**
     * Obtiene estadísticas generales del sistema para el dashboard
     */
    private function obtenerEstadisticasGenerales()
    {
        // Fecha actual (Bogotá)
        $hoy = Carbon::now()->setTimezone('America/Bogota')->format('Y-m-d');
        $inicioSemana = Carbon::now()->setTimezone('America/Bogota')->startOfWeek()->format('Y-m-d');
        $inicioMes = Carbon::now()->setTimezone('America/Bogota')->startOfMonth()->format('Y-m-d');

        // Total de usuarios aprendices
        $totalAprendices = User::where('rol', 'aprendiz')->count();

        // Asistencias de hoy
        $asistenciasHoy = DB::table('asistencias')
            ->whereDate('fecha_hora', $hoy)
            ->where('tipo', 'entrada')
            ->count();
        
        $porcentajeAsistenciaHoy = $totalAprendices > 0 ? round(($asistenciasHoy / $totalAprendices) * 100) : 0;

        // Asistencias de la semana
        $asistenciasSemana = DB::table('asistencias')
            ->whereDate('fecha_hora', '>=', $inicioSemana)
            ->where('tipo', 'entrada')
            ->select(DB::raw('DATE(fecha_hora) as fecha'), DB::raw('count(*) as total'))
            ->groupBy('fecha')
            ->get();

        // Asistencias del mes por programa
        $asistenciasPorPrograma = DB::table('asistencias')
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('programa_formacion', 'programa_formacion.user_id', '=', 'users.id')
            ->whereDate('asistencias.fecha_hora', '>=', $inicioMes)
            ->where('asistencias.tipo', 'entrada')
            ->select(
                'programa_formacion.nombre_programa',
                DB::raw('count(asistencias.id) as total_asistencias')
            )
            ->groupBy('programa_formacion.nombre_programa')
            ->get();

        // Promedio de llegadas tarde (comparando con la hora de entrada de la jornada)
        $llegadasTarde = DB::table('asistencias')
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('jornadas', 'users.jornada_id', '=', 'jornadas.id')
            ->whereDate('asistencias.fecha_hora', '>=', $inicioMes)
            ->where('asistencias.tipo', 'entrada')
            ->whereRaw("TIME(fecha_hora) > TIME(ADDTIME(jornadas.hora_entrada, SEC_TO_TIME(jornadas.tolerancia * 60)))")
            ->count();

        $totalAsistenciasMes = Asistencia::whereDate('fecha_hora', '>=', $inicioMes)
            ->where('tipo', 'entrada')
            ->count();
        
        $porcentajeTardanzas = $totalAsistenciasMes > 0 ? round(($llegadasTarde / $totalAsistenciasMes) * 100) : 0;

        // Tendencia semanal (comparar con semana anterior)
        $inicioSemanaAnterior = Carbon::now()->setTimezone('America/Bogota')->subWeek()->startOfWeek()->format('Y-m-d');
        $finSemanaAnterior = Carbon::now()->setTimezone('America/Bogota')->subWeek()->endOfWeek()->format('Y-m-d');
        
        $asistenciasSemanaAnterior = Asistencia::whereBetween(DB::raw('DATE(fecha_hora)'), [$inicioSemanaAnterior, $finSemanaAnterior])
            ->where('tipo', 'entrada')
            ->count();
        
        $asistenciasSemanaActual = Asistencia::whereBetween(DB::raw('DATE(fecha_hora)'), [$inicioSemana, $hoy])
            ->where('tipo', 'entrada')
            ->count();
        
        $tendenciaSemanal = $asistenciasSemanaAnterior > 0 
            ? round((($asistenciasSemanaActual - $asistenciasSemanaAnterior) / $asistenciasSemanaAnterior) * 100) 
            : 100;

        return [
            'total_aprendices' => $totalAprendices,
            'asistencias_hoy' => $asistenciasHoy,
            'porcentaje_asistencia_hoy' => $porcentajeAsistenciaHoy,
            'asistencias_semana' => $asistenciasSemana,
            'asistencias_por_programa' => $asistenciasPorPrograma,
            'porcentaje_tardanzas' => $porcentajeTardanzas,
            'tendencia_semanal' => $tendenciaSemanal
        ];
    }

    /**
     * Retorna estadísticas para los gráficos del dashboard mediante AJAX
     */
    public function obtenerEstadisticasGraficos()
    {
        // Últimos 7 días
        $ultimosDias = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->setTimezone('America/Bogota')->subDays($i)->format('Y-m-d');
            $ultimosDias[] = $fecha;
        }
        
        // Asistencias por día (últimos 7 días)
        $asistenciasPorDia = [];
        foreach ($ultimosDias as $fecha) {
            $asistenciasPorDia[] = [
                'fecha' => $fecha,
                'total' => Asistencia::whereDate('fecha_hora', $fecha)
                    ->where('tipo', 'entrada')
                    ->count()
            ];
        }
        
        // Asistencias por programa (último mes)
        $inicioMes = Carbon::now()->setTimezone('America/Bogota')->startOfMonth()->format('Y-m-d');
        $asistenciasPorPrograma = DB::table('asistencias')
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->leftJoin('programa_formacion', 'programa_formacion.user_id', '=', 'users.id')
            ->whereDate('asistencias.fecha_hora', '>=', $inicioMes)
            ->where('asistencias.tipo', 'entrada')
            ->select(
                'programa_formacion.nombre_programa',
                DB::raw('count(asistencias.id) as total')
            )
            ->groupBy('programa_formacion.nombre_programa')
            ->get();
        
        // Puntualidad (% en hora vs tarde)
        $puntualidad = $this->calcularEstadisticasPuntualidad();
        
        return response()->json([
            'asistencias_por_dia' => $asistenciasPorDia,
            'asistencias_por_programa' => $asistenciasPorPrograma,
            'puntualidad' => $puntualidad
        ]);
    }
    
    /**
     * Calcula estadísticas de puntualidad (último mes)
     */
    private function calcularEstadisticasPuntualidad()
    {
        $inicioMes = Carbon::now()->setTimezone('America/Bogota')->startOfMonth()->format('Y-m-d');
        
        // Total de asistencias del mes
        $totalAsistencias = Asistencia::whereDate('fecha_hora', '>=', $inicioMes)
            ->where('tipo', 'entrada')
            ->count();
        
        // Llegadas a tiempo
        $llegadasATiempo = DB::table('asistencias')
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('jornadas', 'users.jornada_id', '=', 'jornadas.id')
            ->whereDate('asistencias.fecha_hora', '>=', $inicioMes)
            ->where('asistencias.tipo', 'entrada')
            ->whereRaw("TIME(fecha_hora) <= TIME(ADDTIME(jornadas.hora_entrada, SEC_TO_TIME(jornadas.tolerancia * 60)))")
            ->count();
        
        // Llegadas tarde
        $llegadasTarde = $totalAsistencias - $llegadasATiempo;
        
        return [
            'a_tiempo' => $llegadasATiempo,
            'tarde' => $llegadasTarde,
            'porcentaje_puntualidad' => $totalAsistencias > 0 
                ? round(($llegadasATiempo / $totalAsistencias) * 100) 
                : 0
        ];
    }

    /**
     * Verificar asistencia y mostrar botones correspondientes
     */
    public function verificarAsistencia(Request $request)
    {
        $request->validate([
            'documento_identidad' => 'required|string',
        ]);

        // Buscar al aprendiz
        $user = User::where('documento_identidad', $request->documento_identidad)
            ->where('rol', 'aprendiz')
            ->with(['programaFormacion', 'jornada'])
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Aprendiz no encontrado'], 404);
        }

        // Verificar asistencias del día
        $fechaHoy = now()->setTimezone('America/Bogota')->format('Y-m-d');
        
        $entrada = Asistencia::where('user_id', $user->id)
            ->whereDate('fecha_hora', $fechaHoy)
            ->where('tipo', 'entrada')
            ->exists();
            
        $salida = Asistencia::where('user_id', $user->id)
            ->whereDate('fecha_hora', $fechaHoy)
            ->where('tipo', 'salida')
            ->exists();

        // Verificar horarios
        $horaActual = now()->setTimezone('America/Bogota');
        $fueraHorarioEntrada = $this->estaFueraHorarioEntrada($user->jornada, $horaActual);
        $salidaAnticipada = $this->esSalidaAnticipada($user->jornada, $horaActual);

        return response()->json([
            'user' => $user,
            'puede_registrar_entrada' => !$entrada,
            'puede_registrar_salida' => $entrada && !$salida,
            'requiere_motivo_entrada' => $fueraHorarioEntrada && !$entrada,
            'requiere_motivo_salida' => $salidaAnticipada && $entrada && !$salida
        ]);
    }

    /**
     * Verificar si está fuera del horario de entrada
     */
    private function estaFueraHorarioEntrada($jornada, $horaActual)
    {
        if (!$jornada || !$jornada->hora_entrada) {
            return false;
        }

        $horaEntrada = Carbon::createFromFormat('H:i:s', $jornada->hora_entrada, 'America/Bogota');
        $horaEntrada->setDate($horaActual->year, $horaActual->month, $horaActual->day);
        
        $tolerancia = $jornada->tolerancia ?? 15;
        $horaLimite = $horaEntrada->copy()->addMinutes($tolerancia);

        return $horaActual->gt($horaLimite);
    }

    /**
     * Verificar si es salida anticipada
     */
    private function esSalidaAnticipada($jornada, $horaActual)
    {
        if (!$jornada || !$jornada->hora_salida) {
            return false;
        }

        $horaSalida = Carbon::createFromFormat('H:i:s', $jornada->hora_salida, 'America/Bogota');
        $horaSalida->setDate($horaActual->year, $horaActual->month, $horaActual->day);
        
        // Margen de 20 minutos
        $horaSalidaConMargen = $horaSalida->copy()->subMinutes(20);

        return $horaActual->lt($horaSalidaConMargen);
    }

    /**
     * Registrar asistencia (entrada o salida)
     */
    public function registrarAsistencia(Request $request)
    {
        try {
            // Validación básica
            $request->validate([
                'documento_identidad' => 'required|string',
                'tipo' => 'required|in:entrada,salida',
                'motivo' => 'nullable|string',
                'observaciones' => 'nullable|string'
            ]);

            // Encontrar el usuario
            $user = User::where('documento_identidad', $request->documento_identidad)
                ->where('rol', 'aprendiz')
                ->with('jornada')
                ->first();

            if (!$user) {
                return response()->json(['error' => 'Aprendiz no encontrado'], 404);
            }
            
            // Verificar asistencias del día
            $fechaHoy = now()->setTimezone('America/Bogota')->format('Y-m-d');
            
            $entrada = Asistencia::where('user_id', $user->id)
                ->whereDate('fecha_hora', $fechaHoy)
                ->where('tipo', 'entrada')
                ->exists();
                
            $salida = Asistencia::where('user_id', $user->id)
                ->whereDate('fecha_hora', $fechaHoy)
                ->where('tipo', 'salida')
                ->exists();
                
            // Verificar si puede registrar este tipo de asistencia
            if ($request->tipo === 'entrada' && $entrada) {
                return response()->json(['error' => 'Ya tiene entrada registrada hoy'], 400);
            }
            
            if ($request->tipo === 'salida' && (!$entrada || $salida)) {
                return response()->json(['error' => 'No puede registrar salida'], 400);
            }

            // Verificar condiciones especiales
            $horaActual = now()->setTimezone('America/Bogota');
            $fueraHorarioEntrada = $this->estaFueraHorarioEntrada($user->jornada, $horaActual);
            $salidaAnticipada = $this->esSalidaAnticipada($user->jornada, $horaActual);

            // Crear registro de asistencia
            $asistencia = new Asistencia();
            $asistencia->user_id = $user->id;
            $asistencia->tipo = $request->tipo;
            $asistencia->fecha_hora = $horaActual;
            $asistencia->registrado_por = Auth::id() ?? 1;
            
            // Manejar motivos según el tipo
            if ($request->tipo === 'entrada') {
                $asistencia->fuera_de_horario = $fueraHorarioEntrada;
                if ($fueraHorarioEntrada && $request->has('motivo')) {
                    $asistencia->motivo_entrada = $request->motivo;
                }
            } else { // salida
                $asistencia->salida_anticipada = $salidaAnticipada;
                if ($salidaAnticipada && $request->has('motivo')) {
                    $asistencia->motivo_salida = $request->motivo;
                }
            }
            
            if ($request->has('observaciones')) {
                $asistencia->observaciones = $request->observaciones;
            }
            
            // Guardar foto si existe
            if ($request->hasFile('foto_autorizacion')) {
                $foto = $request->file('foto_autorizacion');
                $nombreFoto = 'autorizacion_' . $user->id . '_' . time() . '.' . $foto->getClientOriginalExtension();
                $foto->storeAs('public/autorizaciones', $nombreFoto);
                $asistencia->foto_autorizacion = $nombreFoto;
            }
            
            // Guardar la asistencia
            $asistencia->save();
            
            return response()->json([
                'message' => 'Asistencia registrada correctamente',
                'tipo' => $request->tipo,
                'hora' => $asistencia->fecha_hora->format('H:i:s'),
                'asistencia' => $asistencia
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en registrarAsistencia: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al registrar la asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar aprendiz por código QR
     */
    public function buscarPorQR(Request $request)
    {
        try {
            $request->validate([
                'qr_code' => 'required|string',
            ]);

            $codigo = trim($request->qr_code);
            
            // Intentar buscar por código QR
            $user = User::where('qr_code', $codigo)
                ->where('rol', 'aprendiz')
                ->first();

            // Si no se encuentra, intentar por documento de identidad
            if (!$user) {
                $documento = preg_replace('/[^0-9]/', '', $codigo);
                
                if (!empty($documento)) {
                    $user = User::where('documento_identidad', $documento)
                        ->where('rol', 'aprendiz')
                        ->first();
                }
            }

            // Si no se encontró usuario
            if (!$user) {
                return response()->json([
                    'error' => 'Aprendiz no encontrado'
                ], 404);
            }

            // Usar verificarAsistencia con el documento encontrado
            return $this->verificarAsistencia(new Request([
                'documento_identidad' => $user->documento_identidad
            ]));

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar el código: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar asistencias en formato Excel o PDF
     */
    public function exportAsistencias(Request $request)
    {
        // Obtener los parámetros de filtro
        $format = $request->get('format', 'excel');
        
        // Construir la consulta base
        $query = Asistencia::query()
            ->with(['user.programaFormacion', 'user.jornada', 'registradoPor']);
            
        // Aplicar filtros si existen
        if ($request->has('fecha_inicio') && !empty($request->fecha_inicio)) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_inicio);
        }
        
        if ($request->has('fecha_fin') && !empty($request->fecha_fin)) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_fin);
        }
        
        if ($request->has('programa_id') && !empty($request->programa_id)) {
            $query->whereHas('user.programaFormacion', function($q) use ($request) {
                $q->where('id', $request->programa_id);
            });
        }
        
        if ($request->has('tipo') && !empty($request->tipo)) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('nombres_completos', 'like', "%$search%")
                  ->orWhere('documento_identidad', 'like', "%$search%");
            });
        }
        
        // Ordenar resultados
        $query->orderBy('fecha_hora', 'desc');
        
        // Obtener todos los resultados para exportación
        $asistencias = $query->get();
        
        // Exportar según formato solicitado
        if ($format == 'excel') {
            // Crear el objeto de exportación
            $export = new AsistenciasExport($asistencias);
            
            // Crear archivo Excel usando la clase
            $fileName = 'asistencias_' . date('Y-m-d_H-i-s') . '.xls';
            
            return Excel::create('Asistencias', function($excel) use ($export) {
                $excel->sheet('Asistencias', function($sheet) use ($export) {
                    $sheet->fromArray($export->getAsistencias());
                    
                    // Dar formato a la cabecera
                    $sheet->row(1, function($row) {
                        $row->setFontWeight('bold');
                    });
                });
            })->download('xls');
        } else {
            $pdf = PDF::loadView('exports.asistencias-pdf', [
                'asistencias' => $asistencias
            ]);
            
            return $pdf->download('asistencias.pdf');
        }
    }

    /**
     * Genera reportes detallados en PDF con toda la información posible
     */
    public function generarReportesPDF(Request $request)
    {
        // Validación de datos
        $request->validate([
            'tipo_reporte' => 'required|in:diario,semanal,mensual,personalizado,programa,jornada,aprendiz',
            'fecha_inicio' => 'required_if:tipo_reporte,personalizado|date',
            'fecha_fin' => 'required_if:tipo_reporte,personalizado|date|after_or_equal:fecha_inicio',
            'programa_id' => 'nullable|exists:programa_formacion,id',
            'jornada_id' => 'nullable|exists:jornadas,id',
            'aprendiz_id' => 'nullable|exists:users,id',
        ]);
        
        // Configurar fechas según el tipo de reporte
        $fechaInicio = null;
        $fechaFin = null;
        $titulo = 'Reporte de Asistencias';
        
        switch($request->tipo_reporte) {
            case 'diario':
                $fechaInicio = Carbon::now()->startOfDay();
                $fechaFin = Carbon::now()->endOfDay();
                $titulo = 'Reporte Diario de Asistencias - ' . $fechaInicio->format('d/m/Y');
                break;
            case 'semanal':
                $fechaInicio = Carbon::now()->startOfWeek();
                $fechaFin = Carbon::now()->endOfWeek();
                $titulo = 'Reporte Semanal de Asistencias - Semana ' . $fechaInicio->weekOfYear;
                break;
            case 'mensual':
                $fechaInicio = Carbon::now()->startOfMonth();
                $fechaFin = Carbon::now()->endOfMonth();
                $titulo = 'Reporte Mensual de Asistencias - ' . $fechaInicio->format('F Y');
                break;
            case 'personalizado':
                $fechaInicio = Carbon::parse($request->fecha_inicio);
                $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
                $titulo = 'Reporte de Asistencias - ' . $fechaInicio->format('d/m/Y') . ' al ' . $fechaFin->format('d/m/Y');
                break;
            case 'programa':
                $fechaInicio = Carbon::now()->subDays(30);
                $fechaFin = Carbon::now();
                $programa = ProgramaFormacion::find($request->programa_id);
                $titulo = 'Reporte de Asistencias - Programa: ' . ($programa ? $programa->nombre_programa : 'Todos');
                break;
            case 'jornada':
                $fechaInicio = Carbon::now()->subDays(30);
                $fechaFin = Carbon::now();
                $jornada = Jornada::find($request->jornada_id);
                $titulo = 'Reporte de Asistencias - Jornada: ' . ($jornada ? $jornada->nombre : 'Todas');
                break;
            case 'aprendiz':
                $fechaInicio = Carbon::now()->subDays(30);
                $fechaFin = Carbon::now();
                $aprendiz = User::find($request->aprendiz_id);
                $titulo = 'Reporte de Asistencias - Aprendiz: ' . ($aprendiz ? $aprendiz->nombres_completos : 'Todos');
                break;
        }
        
        // Construir la consulta base
        $query = Asistencia::query()
            ->with(['user.programaFormacion', 'user.jornada', 'registradoPor']);
            
        // Filtrar por fechas
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_hora', [$fechaInicio, $fechaFin]);
        }
        
        // Aplicar filtros adicionales
        if ($request->programa_id) {
            $query->whereHas('user.programaFormacion', function($q) use ($request) {
                $q->where('id', $request->programa_id);
            });
        }
        
        if ($request->jornada_id) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('jornada_id', $request->jornada_id);
            });
        }
        
        if ($request->aprendiz_id) {
            $query->where('user_id', $request->aprendiz_id);
        }
        
        if ($request->has('tipo') && !empty($request->tipo)) {
            $query->where('tipo', $request->tipo);
        }
        
        // Ordenar resultados
        $query->orderBy('fecha_hora', 'desc');
        
        // Obtener resultados
        $asistencias = $query->get();
        
        // Calcular estadísticas para el reporte
        $estadisticas = $this->calcularEstadisticasParaReporte($asistencias, $fechaInicio, $fechaFin, $request);
        
        // Generar PDF
        $pdf = PDF::loadView('admin.reportes.pdf', [
            'asistencias' => $asistencias,
            'estadisticas' => $estadisticas,
            'titulo' => $titulo,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'programa' => ProgramaFormacion::find($request->programa_id),
                'jornada' => Jornada::find($request->jornada_id),
                'aprendiz' => User::find($request->aprendiz_id),
                'tipo' => $request->tipo
            ]
        ]);
        
        return $pdf->download('reporte_asistencias.pdf');
    }
    
    /**
     * Calcula estadísticas detalladas para los reportes
     */
    private function calcularEstadisticasParaReporte($asistencias, $fechaInicio, $fechaFin, $request)
    {
        // Contar entradas y salidas
        $entradas = $asistencias->where('tipo', 'entrada')->count();
        $salidas = $asistencias->where('tipo', 'salida')->count();
        
        // Contabilizar llegadas tarde y salidas anticipadas
        $llegadasTarde = $asistencias->where('tipo', 'entrada')
                                   ->where('fuera_de_horario', true)
                                   ->count();
        
        $salidasAnticipadas = $asistencias->where('tipo', 'salida')
                                        ->where('salida_anticipada', true)
                                        ->count();
        
        // Porcentajes
        $porcentajePuntualidad = $entradas > 0 ? round(100 - (($llegadasTarde / $entradas) * 100), 2) : 100;
        
        // Asistencias por programa
        $asistenciasPorPrograma = [];
        if (!$request->programa_id) {
            $asistenciasPorPrograma = $asistencias->groupBy(function($asistencia) {
                return $asistencia->user->programaFormacion->nombre_programa ?? 'Sin programa';
            })->map(function($grupo) {
                return [
                    'total' => $grupo->count(),
                    'entradas' => $grupo->where('tipo', 'entrada')->count(),
                    'salidas' => $grupo->where('tipo', 'salida')->count(),
                    'llegadas_tarde' => $grupo->where('tipo', 'entrada')->where('fuera_de_horario', true)->count()
                ];
            });
        }
        
        // Asistencias por día de la semana
        $asistenciasPorDia = $asistencias->groupBy(function($asistencia) {
            $diaSemana = $asistencia->fecha_hora->dayOfWeek;
            $nombresDias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            return $nombresDias[$diaSemana];
        })->map(function($grupo) {
            return [
                'total' => $grupo->count(),
                'entradas' => $grupo->where('tipo', 'entrada')->count()
            ];
        });
        
        return [
            'total_asistencias' => $asistencias->count(),
            'entradas' => $entradas,
            'salidas' => $salidas,
            'llegadas_tarde' => $llegadasTarde,
            'salidas_anticipadas' => $salidasAnticipadas,
            'porcentaje_puntualidad' => $porcentajePuntualidad,
            'asistencias_por_programa' => $asistenciasPorPrograma,
            'asistencias_por_dia' => $asistenciasPorDia,
            'periodo' => [
                'inicio' => $fechaInicio ? $fechaInicio->format('d/m/Y') : 'No especificado',
                'fin' => $fechaFin ? $fechaFin->format('d/m/Y') : 'No especificado'
            ]
        ];
    }

    /**
     * Busca aprendices por nombre o documento para los reportes
     */
    public function buscarAprendices(Request $request)
    {
        $query = $request->input('query');
        
        $aprendices = User::where('rol', 'aprendiz')
            ->where(function($q) use ($query) {
                $q->where('nombres_completos', 'like', "%$query%")
                  ->orWhere('documento_identidad', 'like', "%$query%");
            })
            ->select('id', 'nombres_completos', 'documento_identidad')
            ->limit(10)
            ->get();
            
        return response()->json($aprendices);
    }
    
    /**
     * Filtra aprendices por documento para la vista admin.aprendices.index
     */
    public function filtrarAprendices(Request $request)
    {
        $query = $request->input('query');
        
        $aprendices = User::where('rol', 'aprendiz')
            ->with(['programaFormacion', 'jornada'])
            ->where(function($q) use ($query) {
                $q->where('documento_identidad', 'like', "%$query%");
            })
            ->get();
        
        return response()->json([
            'aprendices' => $aprendices
        ]);
    }

    /**
     * Obtiene los datos de un aprendiz específico
     */
    public function getAprendiz($id)
    {
        $aprendiz = User::where('rol', 'aprendiz')
            ->with(['programaFormacion', 'jornada'])
            ->findOrFail($id);
            
        return response()->json($aprendiz);
    }
    
    /**
     * Obtiene las asistencias de un aprendiz específico
     */
    public function getAprendizAsistencias($id)
    {
        // Obtener últimas 20 asistencias
        $asistencias = Asistencia::where('user_id', $id)
            ->orderBy('fecha_hora', 'desc')
            ->take(20)
            ->get();
            
        return response()->json($asistencias);
    }
    
    /**
     * Obtiene estadísticas de asistencia de un aprendiz específico
     */
    public function getAprendizEstadisticas($id)
    {
        // Obtener todas las asistencias del último mes
        $fechaInicio = Carbon::now()->subDays(30);
        
        $asistencias = Asistencia::where('user_id', $id)
            ->where('fecha_hora', '>=', $fechaInicio)
            ->get();
            
        // Calcular estadísticas
        $totalAsistencias = $asistencias->count();
        $entradas = $asistencias->where('tipo', 'entrada')->count();
        $llegadasTarde = $asistencias->where('tipo', 'entrada')
                                   ->where('fuera_de_horario', true)
                                   ->count();
        $salidasAnticipadas = $asistencias->where('tipo', 'salida')
                                        ->where('salida_anticipada', true)
                                        ->count();
                                        
        // Calcular porcentaje de puntualidad
        $porcentajePuntualidad = $entradas > 0 
            ? round((($entradas - $llegadasTarde) / $entradas) * 100) 
            : 100;
            
        return response()->json([
            'total_asistencias' => $totalAsistencias,
            'llegadas_tarde' => $llegadasTarde,
            'salidas_anticipadas' => $salidasAnticipadas,
            'porcentaje_puntualidad' => $porcentajePuntualidad
        ]);
    }
    
    /**
     * Obtiene los dispositivos registrados de un aprendiz
     */
    public function getAprendizDispositivos($id)
    {
        $dispositivos = [];
        
        // Obtener dispositivos si existe la tabla/modelo
        if (class_exists('App\Models\Device')) {
            $dispositivos = \App\Models\Device::where('user_id', $id)->get();
        }
        
        return response()->json($dispositivos);
    }

    /**
     * Busca aprendices por documento o nombre
     */
    public function buscarAprendiz(Request $request)
    {
        $documento = $request->input('documento', '');
        
        $aprendices = User::where('rol', 'aprendiz')
            ->where(function($query) use ($documento) {
                $query->where('documento_identidad', 'like', "%{$documento}%")
                      ->orWhere('nombres_completos', 'like', "%{$documento}%");
            })
            ->with(['programaFormacion', 'jornada'])
            ->take(20)
            ->get();
        
        return response()->json([
            'aprendices' => $aprendices,
            'total' => count($aprendices)
        ]);
    }
    
    /**
     * Obtiene los detalles de un aprendiz
     */
    public function detallesAprendiz($id)
    {
        $aprendiz = User::with(['programaFormacion', 'jornada'])
            ->where('id', $id)
            ->where('rol', 'aprendiz')
            ->firstOrFail();
        
        // Obtener asistencias
        $asistencias = Asistencia::where('user_id', $id)
            ->orderBy('fecha_hora', 'desc')
            ->take(30)
            ->get()
            ->map(function ($asistencia) {
                return [
                    'fecha' => Carbon::parse($asistencia->fecha_hora)->format('Y-m-d'),
                    'hora' => Carbon::parse($asistencia->fecha_hora)->format('H:i:s'),
                    'tipo' => ucfirst($asistencia->tipo),
                    'estado' => $asistencia->fuera_de_horario ? 'Tarde' : 'A tiempo'
                ];
            });
        
        // Calcular estadísticas
        $totalAsistencias = Asistencia::where('user_id', $id)
            ->where('tipo', 'entrada')
            ->count();
        
        $llegadasTarde = Asistencia::where('user_id', $id)
            ->where('tipo', 'entrada')
            ->where('fuera_de_horario', true)
            ->count();
        
        $salidasAnticipadas = Asistencia::where('user_id', $id)
            ->where('tipo', 'salida')
            ->where('salida_anticipada', true)
            ->count();
            
        $puntualidad = $totalAsistencias > 0 
            ? round(100 - (($llegadasTarde / $totalAsistencias) * 100)) 
            : 100;
        
        $estadisticas = [
            'total_asistencias' => $totalAsistencias,
            'llegadas_tarde' => $llegadasTarde,
            'salidas_anticipadas' => $salidasAnticipadas,
            'puntualidad' => $puntualidad
        ];
        
        return response()->json([
            'aprendiz' => $aprendiz,
            'asistencias' => $asistencias,
            'estadisticas' => $estadisticas
        ]);
    }
}
