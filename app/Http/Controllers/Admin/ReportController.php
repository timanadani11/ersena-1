<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asistencia;
use App\Models\User;
use App\Models\ProgramaFormacion;
use App\Models\Jornada;
use Carbon\Carbon;
use PDF;
use DB;

class ReportController extends Controller
{
    public function index()
    {
        $programas = ProgramaFormacion::orderBy('nombre_programa')->get();
        $jornadas = Jornada::orderBy('nombre')->get();
        
        return view('admin.reportes.index', compact('programas', 'jornadas'));
    }
    
    public function generatePdf(Request $request)
    {
        // Validar datos
        $request->validate([
            'tipo_reporte' => 'required|in:diario,semanal,mensual,personalizado,programa,jornada,aprendiz',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'programa_id' => 'nullable|exists:programas_formacion,id',
            'jornada_id' => 'nullable|exists:jornadas,id',
            'aprendiz_id' => 'nullable|exists:users,id',
            'tipo' => 'nullable|in:entrada,salida',
        ]);
        
        // Check for GD extension
        if (!extension_loaded('gd')) {
            // Continue without GD, the template will handle this
            \Log::warning('PHP GD extension is not installed. PDF will be generated without images.');
        }
        
        // Preparar fechas y título según tipo de reporte
        $fechaInicio = null;
        $fechaFin = null;
        $titulo = '';
        $periodo = '';
        
        switch ($request->tipo_reporte) {
            case 'diario':
                $fechaInicio = Carbon::today();
                $fechaFin = Carbon::today()->endOfDay();
                $titulo = 'Reporte Diario de Asistencias';
                $periodo = $fechaInicio->format('d/m/Y');
                break;
                
            case 'semanal':
                $fechaInicio = Carbon::now()->startOfWeek();
                $fechaFin = Carbon::now()->endOfWeek();
                $titulo = 'Reporte Semanal de Asistencias';
                $periodo = $fechaInicio->format('d/m/Y') . ' al ' . $fechaFin->format('d/m/Y');
                break;
                
            case 'mensual':
                $fechaInicio = Carbon::now()->startOfMonth();
                $fechaFin = Carbon::now()->endOfMonth();
                $titulo = 'Reporte Mensual de Asistencias';
                $periodo = $fechaInicio->locale('es')->monthName . ' ' . $fechaInicio->year;
                break;
                
            case 'personalizado':
                if ($request->fecha_inicio && $request->fecha_fin) {
                    $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
                    $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
                    $titulo = 'Reporte Personalizado de Asistencias';
                    $periodo = $fechaInicio->format('d/m/Y') . ' al ' . $fechaFin->format('d/m/Y');
                } else {
                    return redirect()->back()->with('error', 'Para reportes personalizados debe especificar fecha de inicio y fin.');
                }
                break;
                
            case 'programa':
                $fechaInicio = Carbon::now()->subDays(30);
                $fechaFin = Carbon::now();
                $titulo = 'Reporte por Programa de Formación';
                $periodo = 'Últimos 30 días';
                break;
                
            case 'jornada':
                $fechaInicio = Carbon::now()->subDays(30);
                $fechaFin = Carbon::now();
                $titulo = 'Reporte por Jornada';
                $periodo = 'Últimos 30 días';
                break;
                
            case 'aprendiz':
                $fechaInicio = Carbon::now()->subDays(30);
                $fechaFin = Carbon::now();
                $titulo = 'Reporte por Aprendiz';
                $periodo = 'Últimos 30 días';
                break;
        }
        
        // Construir consulta base
        $query = Asistencia::query()
            ->with(['user', 'user.programaFormacion', 'user.jornada'])
            ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_hora', 'desc');
        
        // Aplicar filtros adicionales
        if ($request->programa_id) {
            $query->whereHas('user.programaFormacion', function($q) use ($request) {
                $q->where('id', $request->programa_id);
            });
        }
        
        if ($request->jornada_id) {
            $query->whereHas('user.jornada', function($q) use ($request) {
                $q->where('id', $request->jornada_id);
            });
        }
        
        if ($request->aprendiz_id) {
            $query->where('user_id', $request->aprendiz_id);
        }
        
        if ($request->tipo) {
            $query->where('tipo', $request->tipo);
        }
        
        // Obtener registros
        $asistencias = $query->get();
        
        // Obtener información adicional para filtros
        $programaNombre = 'Todos';
        if ($request->programa_id) {
            $programa = ProgramaFormacion::find($request->programa_id);
            $programaNombre = $programa ? $programa->nombre_programa : 'No encontrado';
        }
        
        $jornadaNombre = 'Todas';
        if ($request->jornada_id) {
            $jornada = Jornada::find($request->jornada_id);
            $jornadaNombre = $jornada ? $jornada->nombre : 'No encontrada';
        }
        
        $aprendizNombre = 'Todos';
        if ($request->aprendiz_id) {
            $aprendiz = User::find($request->aprendiz_id);
            $aprendizNombre = $aprendiz ? $aprendiz->nombres_completos : 'No encontrado';
        }
        
        // Preparar estadísticas si se requieren
        $estadisticas = null;
        if ($request->has('incluir_estadisticas')) {
            $estadisticas = $this->generarEstadisticas($asistencias);
        }
        
        // Verificar orientación
        $orientacion = $request->has('orientacion_horizontal') ? 'landscape' : 'portrait';
        
        // Generar PDF
        $pdf = PDF::loadView('admin.reportes.pdf', compact(
            'asistencias', 
            'titulo', 
            'periodo', 
            'programaNombre', 
            'jornadaNombre',
            'aprendizNombre',
            'estadisticas',
            'request'
        ));
        
        // Configurar orientación y otras opciones
        $pdf->setPaper('a4', $orientacion);
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);
        
        // Nombre del archivo
        $nombreArchivo = 'Reporte_Asistencias_' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';
        
        // Devolver respuesta
        return $pdf->download($nombreArchivo);
    }
    
    private function generarEstadisticas($asistencias)
    {
        $stats = [
            'total' => $asistencias->count(),
            'entradas' => $asistencias->where('tipo', 'entrada')->count(),
            'salidas' => $asistencias->where('tipo', 'salida')->count(),
            'a_tiempo' => $asistencias->where('fuera_de_horario', false)->where('salida_anticipada', false)->count(),
            'fuera_de_horario' => $asistencias->where('fuera_de_horario', true)->count(),
            'salida_anticipada' => $asistencias->where('salida_anticipada', true)->count(),
            'por_fecha' => [],
            'por_programa' => [],
            'por_jornada' => []
        ];
        
        // Agrupar por fecha
        $porFecha = $asistencias->groupBy(function($item) {
            return $item->fecha_hora->format('Y-m-d');
        });
        
        foreach ($porFecha as $fecha => $items) {
            $stats['por_fecha'][$fecha] = [
                'total' => $items->count(),
                'entradas' => $items->where('tipo', 'entrada')->count(),
                'salidas' => $items->where('tipo', 'salida')->count()
            ];
        }
        
        // Agrupar por programa
        $porPrograma = $asistencias->groupBy(function($item) {
            return $item->user->programaFormacion->nombre_programa ?? 'Sin programa';
        });
        
        foreach ($porPrograma as $programa => $items) {
            $stats['por_programa'][$programa] = [
                'total' => $items->count(),
                'entradas' => $items->where('tipo', 'entrada')->count(),
                'salidas' => $items->where('tipo', 'salida')->count()
            ];
        }
        
        // Agrupar por jornada
        $porJornada = $asistencias->groupBy(function($item) {
            return $item->user->jornada->nombre ?? 'Sin jornada';
        });
        
        foreach ($porJornada as $jornada => $items) {
            $stats['por_jornada'][$jornada] = [
                'total' => $items->count(),
                'entradas' => $items->where('tipo', 'entrada')->count(),
                'salidas' => $items->where('tipo', 'salida')->count()
            ];
        }
        
        return $stats;
    }
    
    public function buscarAprendices(Request $request)
    {
        $query = $request->input('query');
        
        if (strlen($query) < 3) {
            return response()->json([]);
        }
        
        $aprendices = User::where('role', 'aprendiz')
            ->where(function($q) use ($query) {
                $q->where('nombres_completos', 'like', "%{$query}%")
                  ->orWhere('documento_identidad', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'nombres_completos', 'documento_identidad']);
            
        return response()->json($aprendices);
    }
} 