<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramaFormacion extends Model
{
    use HasFactory;

    protected $table = 'programa_formacion';

    protected $fillable = [
        'user_id',
        'jornada_id',
        'nombre_programa',
        'nivel_formacion',
        'numero_ficha',
        'numero_ambiente'
    ];

    protected $casts = [
        'nivel_formacion' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jornada()
    {
        return $this->belongsTo(Jornada::class);
    }
}