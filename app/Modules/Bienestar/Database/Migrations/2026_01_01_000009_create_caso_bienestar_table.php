<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.caso_bienestar', function (Blueprint $table) {
            $table->increments('id_caso');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_tipo_caso');
            $table->unsignedInteger('id_usuario_registro');
            $table->text('descripcion');
            $table->string('estado', 20)->default('registrado');
            $table->string('prioridad', 15)->default('media');
            $table->boolean('confidencial')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();

            $table->foreign('id_estudiante', 'fk_caso_estudiante')
                ->references('id_estudiante')
                ->on('bienestar.estudiante')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_tipo_caso', 'fk_caso_tipo')
                ->references('id_tipo_caso')
                ->on('bienestar.tipo_caso')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_usuario_registro', 'fk_caso_usuario_reg')
                ->references('id_usuario')
                ->on('bienestar.usuario')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('id_estudiante', 'idx_caso_estudiante');
            $table->index('estado', 'idx_caso_estado');
            $table->index('confidencial', 'idx_caso_confidencial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.caso_bienestar');
    }
};
