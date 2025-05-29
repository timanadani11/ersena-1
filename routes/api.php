<?php

use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ProgramaFormacionController;
use App\Http\Controllers\TickerMessageController;
use Illuminate\Support\Facades\Route;

Route::get('/asistencias/diarias', [AsistenciaController::class, 'getAsistenciasDiarias']);

Route::get('/programas', [ProgramaFormacionController::class, 'index']);
Route::get('/programas/search', [ProgramaFormacionController::class, 'search']);
Route::get('/ticker-messages', [TickerMessageController::class, 'getMessages']);
Route::get('/ticker-messages/diagnose', [TickerMessageController::class, 'diagnose']);
