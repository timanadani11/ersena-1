<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AprendizController;
use App\Http\Controllers\AsistenciaController;

// Ruta para verificar el estado de la sesión
Route::get('/check-session', function () {
    return response()->json(['authenticated' => auth()->check()]);
})->middleware('web');

Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/home', function () {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('aprendiz.dashboard');
    })->name('home');
});

// Rutas de administrador
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Escáner QR
    Route::get('/scanner', [AdminController::class, 'scanner'])->name('admin.scanner');
    
    // Aprendices
    Route::get('/aprendices', [AdminController::class, 'aprendices'])->name('admin.aprendices');
    
    // Programas
    Route::get('/programas', [AdminController::class, 'programas'])->name('admin.programas');
    
    // Asistencias
    Route::get('/asistencias', [AdminController::class, 'asistencias'])->name('admin.asistencias.index');
    
    // Reportes
    Route::get('/reportes', [AdminController::class, 'reportes'])->name('admin.reportes');
    
    // Configuración
    Route::get('/configuracion', [AdminController::class, 'configuracion'])->name('admin.configuracion');

    // API de estadísticas para gráficos AJAX
    Route::get('/api/estadisticas/graficos', [AdminController::class, 'obtenerEstadisticasGraficos'])->name('admin.estadisticas.graficos');
    
    // Verificar asistencia por documento
    Route::post('/verificar-asistencia', [AdminController::class, 'verificarAsistencia'])->name('admin.verificar-asistencia');
    
    // Buscar por código QR
    Route::post('/buscar-por-qr', [AdminController::class, 'buscarPorQR'])->name('admin.buscar-por-qr');
    
    // Registrar asistencia
    Route::post('/registrar-asistencia', [AdminController::class, 'registrarAsistencia'])->name('admin.registrar-asistencia');
});

Route::middleware(['auth', 'role:aprendiz'])->group(function () {
    Route::get('/aprendiz/dashboard', [AprendizController::class, 'dashboard'])->name('aprendiz.dashboard');
    Route::post('/aprendiz/actualizar-foto', [AprendizController::class, 'actualizarFoto'])->name('aprendiz.actualizar-foto');
    Route::post('/aprendiz/filtrar-registros', [AprendizController::class, 'filtrarRegistros'])->name('aprendiz.filtrar-registros');
});
