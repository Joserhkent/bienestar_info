<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.tipo_caso', function (Blueprint $table) {
            $table->increments('id_tipo_caso');
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.tipo_caso');
    }
};
