<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.notificacion', function (Blueprint $table) {
            $table->increments('id_notificacion');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_caso')->nullable();
            $table->string('titulo', 120);
            $table->text('mensaje');
            $table->string('canal', 20)->default('sistema');
            $table->timestamp('fecha_envio')->useCurrent();
            $table->boolean('leida')->default(false);

            $table->foreign('id_usuario', 'fk_notificacion_usuario')
                ->references('id_usuario')
                ->on('bienestar.usuario')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_caso', 'fk_notificacion_caso')
                ->references('id_caso')
                ->on('bienestar.caso_bienestar')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['id_usuario', 'leida'], 'idx_notificacion_usuario_leida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.notificacion');
    }
};
