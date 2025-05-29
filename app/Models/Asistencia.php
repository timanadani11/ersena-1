<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'user_id',
        'tipo',
        'fecha_hora',
        'registrado_por',
        'salida_anticipada',
        'fuera_de_horario',
        'motivo_salida',
        'motivo_entrada',
        'observaciones',
        'foto_autorizacion'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'salida_anticipada' => 'boolean',
        'fuera_de_horario' => 'boolean',
    ];

    /**
     * Relación con el usuario al que pertenece la asistencia
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el usuario que registró la asistencia (puede ser un administrador)
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Determina si la asistencia es una llegada tarde
     */
    public function getEsTardeAttribute()
    {
        // Solo aplica para entradas
        if ($this->tipo !== 'entrada') {
            return false;
        }
        
        // Obtener la jornada del usuario
        $jornada = $this->user->jornada;
        if (!$jornada) {
            return false;
        }
        
        // Obtener la hora de entrada de la jornada
        $horaEntrada = $jornada->hora_entrada;
        if (!$horaEntrada) {
            return false;
        }
        
        // Convertir a objetos Carbon para comparar
        $horaLimite = \Carbon\Carbon::createFromFormat('H:i:s', $horaEntrada);
        
        // Aplicar tolerancia (si existe)
        $tolerancia = $jornada->tolerancia ?? 0;
        $horaLimite->addMinutes($tolerancia);
        
        // Obtener solo la parte de hora:minuto:segundo del registro de asistencia
        $horaRegistro = \Carbon\Carbon::createFromFormat(
            'H:i:s', 
            $this->fecha_hora->format('H:i:s')
        );
        
        // Es tarde si la hora de registro es posterior a la hora límite
        return $horaRegistro->gt($horaLimite);
    }
}