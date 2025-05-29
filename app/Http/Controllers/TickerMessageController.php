<?php

namespace App\Http\Controllers;

use App\Services\TickerMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TickerMessageController extends Controller
{
    private TickerMessageService $tickerMessageService;
    private $cacheKey = 'ticker_messages';
    private $cacheDuration = 600; // 10 minutos para actualización periódica

    public function __construct(TickerMessageService $tickerMessageService)
    {
        $this->tickerMessageService = $tickerMessageService;
    }

    public function getMessages(): JsonResponse
    {
        try {
            // Borrar caché para desarrollo/diagnóstico si es necesario
            // Cache::forget($this->cacheKey . '_' . now()->format('YmdH'));
            
            // Intentar obtener mensajes del caché primero para respuesta rápida
            // Pero usar un identificador único basado en la hora para rotar mensajes
            $uniqueHourKey = $this->cacheKey . '_' . now()->format('YmdH');
            
            $cachedMessages = Cache::get($uniqueHourKey);
            
            if ($cachedMessages && !empty($cachedMessages)) {
                // Si tenemos mensajes en caché, devolverlos inmediatamente
                // pero añadir un poco de variedad en el orden
                shuffle($cachedMessages);
                
                return response()->json([
                    'status' => 'success',
                    'messages' => $cachedMessages,
                    'count' => count($cachedMessages),
                    'updateInterval' => 8000, // 8 segundos
                    'source' => 'cache'
                ]);
            }
            
            // Si no hay caché, obtener mensajes frescos
            $messages = $this->tickerMessageService->getMensajes();
            
            // Verificar que los mensajes no estén vacíos
            if (empty($messages)) {
                Log::warning('Se generaron mensajes vacíos desde el servicio');
                $messages = $this->getMensajesEmergencia();
            }
            
            // Guardar en caché para futuras solicitudes
            Cache::put($uniqueHourKey, $messages, $this->cacheDuration);
            
            return response()->json([
                'status' => 'success',
                'messages' => $messages,
                'count' => count($messages),
                'updateInterval' => 8000, // 8 segundos
                'source' => 'fresh'
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo mensajes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Intentar obtener mensajes del servicio incluso si hay error
            $messages = [];
            try {
                // Intentar primero obtener del caché de emergencia
                $messages = Cache::get($this->cacheKey . '_emergency');
                
                if (empty($messages)) {
                    // Usar mensajes de emergencia
                    $messages = $this->getMensajesEmergencia();
                    
                    // Y guardarlos como caché de emergencia
                    if (!empty($messages)) {
                        Cache::put($this->cacheKey . '_emergency', $messages, 3600); // 1 hora
                    }
                }
            } catch (\Exception $inner) {
                Log::error('Error en segundo intento:', ['error' => $inner->getMessage()]);
                $messages = $this->getMensajesEmergencia();
            }

            return response()->json([
                'status' => 'warning',
                'messages' => $messages,
                'count' => count($messages),
                'updateInterval' => 5000, // 5 segundos en caso de error para actualizar más rápido
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Método de diagnóstico para revisar los mensajes generados
     */
    public function diagnose(): JsonResponse
    {
        try {
            // Forzar una generación fresca de mensajes
            $messages = $this->tickerMessageService->getMensajes();
            
            // Obtener información de cada categoría
            $motivacionales = $this->tickerMessageService->generarMensajesMotivacionales();
            $institucionales = $this->tickerMessageService->generarMensajesInstitucionales();
            
            return response()->json([
                'status' => 'success',
                'messages_count' => count($messages),
                'motivacionales_count' => count($motivacionales),
                'institucionales_count' => count($institucionales),
                'sample_messages' => array_slice($messages, 0, 10),
                'php_version' => phpversion(),
                'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
                'time' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Proporciona mensajes de emergencia cuando todo lo demás falla
     */
    private function getMensajesEmergencia(): array
    {
        return [
            "👋 ¡Bienvenidos al SENA!",
            "💻 Sistema de Control de Asistencia",
            "⚠️ Actualizando información...",
            "📚 La formación es la base del desarrollo profesional",
            "🌟 El éxito comienza con la disciplina diaria",
            "🔧 Trabajando para ofrecerte la mejor experiencia",
            "🎓 El SENA forma el talento humano de Colombia",
            "🌱 Cada día es una oportunidad para aprender algo nuevo",
            "⏰ La puntualidad es una muestra de respeto hacia los demás",
            "🔍 El conocimiento es el camino hacia mejores oportunidades",
            "💪 Con esfuerzo y dedicación, todo es posible",
            "🤝 El trabajo en equipo es clave para el éxito profesional",
            "📱 La tecnología nos conecta y nos abre nuevas posibilidades",
            "🧠 Ejercita tu mente aprendiendo nuevas habilidades"
        ];
    }
}
