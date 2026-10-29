<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.cita', function (Blueprint $table) {
            $table->increments('id_cita');
            $table->unsignedInteger('id_horario');
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_caso')->nullable();
            $table->string('tipo_cita', 30);
            $table->string('estado', 20)->default('solicitada');
            $table->string('motivo', 255);
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->timestamp('fecha_confirmacion')->nullable();

            $table->foreign('id_horario', 'fk_cita_horario')
                ->references('id_horario')
                ->on('bienestar.horario_disponible')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_estudiante', 'fk_cita_estudiante')
                ->references('id_estudiante')
                ->on('bienestar.estudiante')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_caso', 'fk_cita_caso')
                ->references('id_caso')
                ->on('bienestar.caso_bienestar')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('id_estudiante', 'idx_cita_estudiante');
            $table->index('id_horario', 'idx_cita_horario');
            $table->index('estado', 'idx_cita_estado');
        });

        // Índice único parcial exigido: permite reutilizar el horario si la cita previa fue cancelada
        DB::statement("CREATE UNIQUE INDEX uq_cita_horario_activo ON bienestar.cita (id_horario) WHERE estado != 'cancelada';");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS bienestar.uq_cita_horario_activo;");
        Schema::dropIfExists('bienestar.cita');
    }
};
