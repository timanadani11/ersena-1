<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Asistencia;
use App\Models\Device;
use App\Models\ProgramaFormacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AprendizController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Obtener programa de formación
        $programa = ProgramaFormacion::where('user_id', $user->id)->first();
        
        // Obtener dispositivos
        $devices = Device::where('user_id', $user->id)->get();
        
        // Obtener último registro de asistencia
        $ultimoRegistro = Asistencia::where('user_id', $user->id)
            ->latest('fecha_hora')
            ->first();
        
        // Obtener registros recientes (último mes)
        $registrosRecientes = Asistencia::where('user_id', $user->id)
            ->whereBetween('fecha_hora', [Carbon::now()->subMonth(), Carbon::now()])
            ->orderBy('fecha_hora', 'desc')
            ->take(5)
            ->get();
        
        // Determinar estado actual
        $estadoActual = $this->determinarEstadoActual($ultimoRegistro);
        
        // Obtener todos los registros para la sección de registros
        $registros = Asistencia::where('user_id', $user->id)
            ->orderBy('fecha_hora', 'desc')
            ->paginate(20);

        return view('aprendiz.dashboard', compact(
            'user',
            'programa',
            'devices',
            'ultimoRegistro',
            'registrosRecientes',
            'estadoActual',
            'registros'
        ));
    }

    private function determinarEstadoActual($ultimoRegistro)
    {
        if (!$ultimoRegistro) {
            return 'fuera';
        }

        return $ultimoRegistro->tipo === 'entrada' ? 'dentro' : 'fuera';
    }

    public function actualizarFoto(Request $request)
    {
        try {
            $request->validate([
                'foto_perfil' => 'required|string'
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Update and save the user model
            User::where('id', $user->id)->update([
                'profile_photo' => $request->foto_perfil
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud'
            ], 500);
        }
    }

    public function filtrarRegistros(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        $registros = Asistencia::where('user_id', Auth::id())
            ->whereBetween('fecha_hora', [
                Carbon::parse($request->fecha_inicio)->startOfDay(),
                Carbon::parse($request->fecha_fin)->endOfDay()
            ])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'registros' => $registros
        ]);
    }
}


