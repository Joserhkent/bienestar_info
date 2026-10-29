<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.perfil_socioeconomico', function (Blueprint $table) {
            $table->increments('id_perfil');
            $table->unsignedInteger('id_estudiante')->unique();
            $table->string('clasificacion_sisfoh', 25);
            $table->boolean('violencia_familiar')->default(false);
            $table->string('situacion_orfandad', 20)->default('ninguna');
            $table->boolean('es_sustento_familiar')->default(false);
            $table->unsignedSmallInteger('nro_dependientes')->default(0);
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_actualizacion')->useCurrent();

            $table->foreign('id_estudiante', 'fk_perfil_estudiante')
                ->references('id_estudiante')
                ->on('bienestar.estudiante')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.perfil_socioeconomico');
    }
};
