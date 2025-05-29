<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class AsistenciasExport {
    protected $asistencias;

    /**
     * Constructor to set the data property
     */
    public function __construct($asistencias)
    {
        $this->asistencias = $asistencias;
    }

    /**
     * Get the data for export
     */
    public function getAsistencias()
    {
        $data = [];
        
        // Add header row
        $data[] = [
            'Fecha y Hora',
            'Documento',
            'Aprendiz',
            'Programa',
            'Ficha',
            'Tipo',
            'Estado',
            'Registrado por',
            'Observaciones'
        ];
        
        // Add data rows
        foreach ($this->asistencias as $asistencia) {
            $estado = 'A tiempo';
            if ($asistencia->tipo === 'entrada' && $asistencia->fuera_de_horario) {
                $estado = 'Tarde';
            } elseif ($asistencia->tipo === 'salida' && $asistencia->salida_anticipada) {
                $estado = 'Salida anticipada';
            }
            
            $data[] = [
                $asistencia->fecha_hora->format('d/m/Y H:i'),
                $asistencia->user->documento_identidad ?? 'N/A',
                $asistencia->user->nombres_completos ?? 'N/A',
                $asistencia->user->programaFormacion->nombre_programa ?? 'N/A',
                $asistencia->user->programaFormacion->numero_ficha ?? 'N/A',
                $asistencia->tipo === 'entrada' ? 'Entrada' : 'Salida',
                $estado,
                $asistencia->registradoPor->nombres_completos ?? 'Sistema',
                $asistencia->observaciones ?? ''
            ];
        }
        
        return $data;
    }
} 