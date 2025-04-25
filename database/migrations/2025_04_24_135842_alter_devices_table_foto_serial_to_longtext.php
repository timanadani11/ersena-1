<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->longText('foto_serial')->change();
        });
    }

    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('foto_serial')->change();
        });
    }
};
