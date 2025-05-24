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
            // Validación mejorada
            $request->validate([
                'foto_perfil' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ], [
                'foto_perfil.required' => 'Debes seleccionar una imagen',
                'foto_perfil.image' => 'El archivo debe ser una imagen',
                'foto_perfil.mimes' => 'Solo se permiten archivos JPG, PNG, GIF o WEBP',
                'foto_perfil.max' => 'La imagen no puede superar los 2MB'
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Eliminar foto anterior si existe y no es la imagen por defecto
            if ($user->profile_photo && 
                $user->profile_photo !== 'img/default/default.png' && 
                Storage::disk('public')->exists(str_replace('storage/', '', $user->profile_photo))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $user->profile_photo));
            }

            // Guardar la nueva imagen
            $file = $request->file('foto_perfil');
            $fileName = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_photos', $fileName, 'public');

            // Actualizar el usuario
            $user->profile_photo = 'storage/' . $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto actualizada correctamente',
                'new_photo_url' => asset($user->profile_photo)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar foto de perfil: ' . $e->getMessage());
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