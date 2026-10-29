<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.estudiante', function (Blueprint $table) {
            $table->increments('id_estudiante');
            $table->unsignedInteger('id_usuario')->unique();
            $table->string('codigo_universitario', 10)->unique();
            $table->string('dni', 8)->unique();
            $table->string('nombres', 80);
            $table->string('apellidos', 80);
            $table->char('sexo', 1);
            $table->date('fecha_nacimiento');
            $table->string('telefono', 15)->nullable();
            $table->string('direccion_actual', 255)->nullable();
            $table->string('zona_procedencia', 50);
            $table->string('lugar_procedencia', 100);
            $table->unsignedTinyInteger('ciclo_actual');
            $table->timestamp('fecha_registro')->useCurrent();

            $table->foreign('id_usuario', 'fk_estudiante_usuario')
                ->references('id_usuario')
                ->on('bienestar.usuario')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('codigo_universitario', 'idx_estudiante_codigo');
            $table->index('dni', 'idx_estudiante_dni');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.estudiante');
    }
};
