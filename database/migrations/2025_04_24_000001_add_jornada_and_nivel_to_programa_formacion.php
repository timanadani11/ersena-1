<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('programa_formacion', function (Blueprint $table) {
            $table->foreignId('jornada_id')->nullable()->after('user_id')->constrained('jornadas');
            $table->enum('nivel_formacion', ['tecnico', 'tecnologo'])->after('nombre_programa');
        });
    }

    public function down()
    {
        Schema::table('programa_formacion', function (Blueprint $table) {
            $table->dropForeign(['jornada_id']);
            $table->dropColumn(['jornada_id', 'nivel_formacion']);
        });
    }
};