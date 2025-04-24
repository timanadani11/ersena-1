<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Asistencia extends Model
{
    protected $table = 'asistencias';

    protected $fillable = [
        'user_id',
        'tipo',
        'fecha_hora',
        'registrado_por'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    // Relación con el usuario (aprendiz)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el usuario que registró la asistencia
    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}