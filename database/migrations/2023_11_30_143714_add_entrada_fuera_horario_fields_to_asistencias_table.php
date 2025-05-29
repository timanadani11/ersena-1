<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->boolean('fuera_de_horario')->default(false)->after('salida_anticipada');
            $table->string('motivo_entrada')->nullable()->after('motivo_salida');
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn('fuera_de_horario');
            $table->dropColumn('motivo_entrada');
        });
    }
}; 