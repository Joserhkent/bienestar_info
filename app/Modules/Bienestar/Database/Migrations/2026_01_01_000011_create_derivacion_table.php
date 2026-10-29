<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.derivacion', function (Blueprint $table) {
            $table->increments('id_derivacion');
            $table->unsignedInteger('id_caso');
            $table->unsignedInteger('id_especialista');
            $table->unsignedInteger('id_usuario_deriva');
            $table->text('motivo');
            $table->string('estado', 20)->default('pendiente');
            $table->timestamp('fecha_derivacion')->useCurrent();

            $table->foreign('id_caso', 'fk_derivacion_caso')
                ->references('id_caso')
                ->on('bienestar.caso_bienestar')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_especialista', 'fk_derivacion_especialista')
                ->references('id_especialista')
                ->on('bienestar.especialista')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_usuario_deriva', 'fk_derivacion_usuario_deriva')
                ->references('id_usuario')
                ->on('bienestar.usuario')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('id_caso', 'idx_derivacion_caso');
            $table->index('id_especialista', 'idx_derivacion_especialista');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.derivacion');
    }
};
