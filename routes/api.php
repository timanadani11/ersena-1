<?php

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/asistencias/diarias', [AsistenciaController::class, 'getAsistenciasDiarias']);
