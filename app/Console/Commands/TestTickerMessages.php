<?php

namespace App\Console\Commands;

use App\Services\TickerMessageService;
use Illuminate\Console\Command;

class TestTickerMessages extends Command
{
    protected $signature = 'ticker:test';
    protected $description = 'Prueba los mensajes del ticker para verificar su funcionamiento';

    private TickerMessageService $tickerService;

    public function __construct(TickerMessageService $tickerService)
    {
        parent::__construct();
        $this->tickerService = $tickerService;
    }

    public function handle()
    {
        $this->info('Probando mensajes del ticker...');
        
        // Datos específicos de la base de datos
        $this->info("\n--- MENSAJES BASADOS EN DATOS DE LA BASE DE DATOS ---");
        
        // Formaciones por jornada
        $this->info("\nFormaciones por jornada:");
        $formacionesPorJornada = $this->tickerService->getFormacionesPorJornada();
        foreach ($formacionesPorJornada as $index => $formacion) {
            $this->line(($index + 1) . ". Jornada {$formacion['jornada']}: {$formacion['total_programas']} programas");
        }
        
        // Total de aprendices con portátiles
        $this->info("\nTotal de aprendices con portátiles:");
        $totalAprendices = $this->tickerService->getTotalAprendicesConPortatiles();
        $this->line("Total: $totalAprendices aprendices");
        
        // Datos curiosos por programa
        $this->info("\nDatos curiosos por programa:");
        $datosCuriososProgramas = $this->tickerService->getDatosCuriososProgramas();
        foreach (array_slice($datosCuriososProgramas, 0, 5) as $index => $mensaje) {
            $this->line(($index + 1) . ". $mensaje");
        }
        
        $this->info("\n--- MENSAJES PREDEFINIDOS POR CATEGORÍA ---");
        
        // Mostrar algunos mensajes de cada categoría predefinida
        $categorias = [
            'Mensajes Institucionales' => $this->tickerService->generarMensajesInstitucionales(),
            'Mensajes Motivacionales' => $this->tickerService->generarMensajesMotivacionales(),
            'Mensajes de Tecnología' => $this->tickerService->generarMensajesTecnologia(),
            'Mensajes de Formación' => $this->tickerService->generarMensajesFormacion(),
            'Mensajes de Hábitos' => $this->tickerService->generarMensajesHabitos(),
            'Mensajes de Empleabilidad' => $this->tickerService->generarMensajesEmpleabilidad(),
            'Datos Curiosos' => $this->tickerService->generarDatosCuriosos()
        ];
        
        foreach ($categorias as $titulo => $mensajes) {
            $this->showCategory($titulo, $mensajes, 3);
        }
        
        // Obtener todos los mensajes mezclados
        $this->info("\nObteniendo todos los mensajes mezclados:");
        $allMessages = $this->tickerService->getMensajes();
        $this->info("Total de mensajes generados: " . count($allMessages));
        
        // Mostrar una muestra de los mensajes mezclados
        $this->info("\nMuestra de mensajes combinados:");
        foreach (array_slice($allMessages, 0, 10) as $index => $message) {
            $this->line(($index + 1) . ". " . $message);
        }
        
        return Command::SUCCESS;
    }
    
    private function showCategory(string $title, array $messages, int $count)
    {
        $this->info("\n$title:");
        shuffle($messages);
        foreach (array_slice($messages, 0, $count) as $index => $message) {
            $this->line(($index + 1) . ". " . $message);
        }
    }
} 