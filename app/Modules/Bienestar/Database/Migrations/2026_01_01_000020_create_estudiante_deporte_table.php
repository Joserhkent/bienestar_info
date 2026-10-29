<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.estudiante_deporte', function (Blueprint $table) {
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_deporte');
            $table->string('nivel', 20);
            $table->boolean('disponible_seleccion')->default(true);

            $table->primary(['id_estudiante', 'id_deporte']);

            $table->foreign('id_estudiante', 'fk_pivot_deporte_estudiante')
                ->references('id_estudiante')
                ->on('bienestar.estudiante')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_deporte', 'fk_pivot_deporte_deporte')
                ->references('id_deporte')
                ->on('bienestar.deporte')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('disponible_seleccion', 'idx_pivot_deporte_seleccion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.estudiante_deporte');
    }
};
