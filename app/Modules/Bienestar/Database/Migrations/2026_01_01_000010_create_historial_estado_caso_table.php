<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.historial_estado_caso', function (Blueprint $table) {
            $table->increments('id_historial');
            $table->unsignedInteger('id_caso');
            $table->unsignedInteger('id_usuario');
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->text('comentario')->nullable();

            $table->foreign('id_caso', 'fk_historial_caso')
                ->references('id_caso')
                ->on('bienestar.caso_bienestar')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_usuario', 'fk_historial_usuario')
                ->references('id_usuario')
                ->on('bienestar.usuario')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('id_caso', 'idx_historial_caso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.historial_estado_caso');
    }
};
