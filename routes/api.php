<?php

use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ProgramaFormacionController;
use Illuminate\Support\Facades\Route;

Route::get('/asistencias/diarias', [AsistenciaController::class, 'getAsistenciasDiarias']);

Route::get('/programas', [ProgramaFormacionController::class, 'index']);
Route::get('/programas/search', [ProgramaFormacionController::class, 'search']);
Route::get('/ticker-messages', [App\Http\Controllers\TickerMessageController::class, 'getMessages']);
