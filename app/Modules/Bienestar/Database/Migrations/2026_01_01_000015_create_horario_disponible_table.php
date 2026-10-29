<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.horario_disponible', function (Blueprint $table) {
            $table->increments('id_horario');
            $table->unsignedInteger('id_especialista');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('estado', 20)->default('disponible');

            $table->foreign('id_especialista', 'fk_horario_especialista')
                ->references('id_especialista')
                ->on('bienestar.especialista')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index(['id_especialista', 'fecha'], 'idx_horario_especialista_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.horario_disponible');
    }
};
