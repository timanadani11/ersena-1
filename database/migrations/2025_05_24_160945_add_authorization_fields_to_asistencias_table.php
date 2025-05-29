<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->text('observaciones')->nullable();
            $table->string('foto_autorizacion')->nullable();
            $table->boolean('salida_anticipada')->default(false);
            $table->string('motivo_salida')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn('observaciones');
            $table->dropColumn('foto_autorizacion');
            $table->dropColumn('salida_anticipada');
            $table->dropColumn('motivo_salida');
        });
    }
};
