<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Models\ProgramaFormacion;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombres_completos' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:255|unique:users',
            'correo' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nombre_programa' => 'required|string|max:255',
            'nivel_formacion' => 'required|in:tecnico,tecnologo',
            'numero_ficha' => 'required|string|max:255',
            'numero_ambiente' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'serial' => 'required|string|max:255',
            'foto_serial' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'jornada_id' => 'required|exists:jornadas,id',
        ]);

        // Generar un identificador único para el QR
        $qrIdentifier = $validated['documento_identidad'];

        // Handle file upload
        $fotoSerialPath = null;
        if ($request->hasFile('foto_serial')) {
            $file = $request->file('foto_serial');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            // Store in 'storage/app/public/seriales'
            $fotoSerialPath = $file->storeAs('seriales', $filename, 'public');
        }

        $user = User::create([
            'nombres_completos' => $validated['nombres_completos'],
            'documento_identidad' => $validated['documento_identidad'],
            'correo' => $validated['correo'],
            'password' => Hash::make($validated['password']),
            'rol' => 'aprendiz',
            'qr_code' => $qrIdentifier,
            'jornada_id' => $validated['jornada_id'],
            'profile_photo' => 'img/default/default.png',
        ]);

        // Crear el registro del programa de formación
        ProgramaFormacion::create([
            'user_id' => $user->id,
            'jornada_id' => $validated['jornada_id'],
            'nombre_programa' => $validated['nombre_programa'],
            'nivel_formacion' => $validated['nivel_formacion'],
            'numero_ficha' => $validated['numero_ficha'],
            'numero_ambiente' => $validated['numero_ambiente'],
        ]);

        Device::create([
            'user_id' => $user->id,
            'marca' => $validated['marca'],
            'serial' => $validated['serial'],
            'foto_serial' => $fotoSerialPath ? Storage::url($fotoSerialPath) : null,
        ]);

        // Enviar correo de bienvenida
        try {
            Mail::to($user->correo)->send(new WelcomeEmail($user));
        } catch (Exception $e) {
            Log::error("Error enviando correo de bienvenida: " . $e->getMessage());
            // Opcional: Podrías querer notificar al usuario de alguna manera o reintentar
        }

        return redirect()->route('login')
            ->with('success', 'Registro exitoso. Por favor inicia sesión para acceder a tu código QR.');
    }
}
