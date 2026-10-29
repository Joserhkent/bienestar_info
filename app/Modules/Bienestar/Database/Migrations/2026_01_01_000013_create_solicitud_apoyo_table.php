<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.solicitud_apoyo', function (Blueprint $table) {
            $table->increments('id_solicitud');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_usuario_revisa')->nullable();
            $table->string('tipo_apoyo', 50);
            $table->text('motivo');
            $table->string('estado', 20)->default('enviada');
            $table->timestamp('fecha_envio')->useCurrent();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->text('observacion')->nullable();

            $table->foreign('id_estudiante', 'fk_solicitud_estudiante')
                ->references('id_estudiante')
                ->on('bienestar.estudiante')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_usuario_revisa', 'fk_solicitud_usuario_revisa')
                ->references('id_usuario')
                ->on('bienestar.usuario')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('id_estudiante', 'idx_solicitud_estudiante');
            $table->index('estado', 'idx_solicitud_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.solicitud_apoyo');
    }
};
