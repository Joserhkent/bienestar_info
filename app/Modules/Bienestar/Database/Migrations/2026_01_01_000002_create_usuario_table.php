<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.usuario', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->unsignedInteger('id_rol');
            $table->string('correo', 100)->unique();
            $table->string('contrasena_hash', 255);
            $table->string('estado', 20)->default('activo');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('ultimo_acceso')->nullable();

            $table->foreign('id_rol', 'fk_usuario_rol')
                ->references('id_rol')
                ->on('bienestar.rol')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('correo', 'idx_usuario_correo');
            $table->index('id_rol', 'idx_usuario_rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.usuario');
    }
};
