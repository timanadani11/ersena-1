<?php

namespace App\Http\Controllers;

use App\Services\TickerMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TickerMessageController extends Controller
{
    private TickerMessageService $tickerMessageService;

    public function __construct(TickerMessageService $tickerMessageService)
    {
        $this->tickerMessageService = $tickerMessageService;
    }

    public function getMessages(): JsonResponse
    {
        try {
            $messages = $this->tickerMessageService->getMensajes();
            
            return response()->json([
                'status' => 'success',
                'messages' => $messages,
                'count' => count($messages),
                'updateInterval' => 8000 // 8 segundos
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo mensajes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Intentar obtener mensajes del servicio incluso si hay error
            $messages = [];
            try {
                $messages = $this->tickerMessageService->getMensajes();
            } catch (\Exception $inner) {
                Log::error('Error en segundo intento:', ['error' => $inner->getMessage()]);
                $messages = [
                    "👋 ¡Bienvenidos al SENA!",
                    "💻 Sistema de Control de Asistencia",
                    "⚠️ Actualizando información..."
                ];
            }

            return response()->json([
                'status' => 'warning',
                'messages' => $messages,
                'count' => count($messages),
                'updateInterval' => 5000 // 5 segundos en caso de error para actualizar más rápido
            ]);
        }
    }
}
