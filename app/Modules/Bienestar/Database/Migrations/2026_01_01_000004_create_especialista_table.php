<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.especialista', function (Blueprint $table) {
            $table->increments('id_especialista');
            $table->unsignedInteger('id_usuario')->unique();
            $table->string('nombres', 80);
            $table->string('apellidos', 80);
            $table->string('especialidad', 100);
            $table->string('nro_colegiatura', 20)->unique();
            $table->string('estado', 20)->default('activo');

            $table->foreign('id_usuario', 'fk_especialista_usuario')
                ->references('id_usuario')
                ->on('bienestar.usuario')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('nro_colegiatura', 'idx_especialista_colegiatura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.especialista');
    }
};
