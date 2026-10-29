<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.documento_adjunto', function (Blueprint $table) {
            $table->increments('id_documento');
            $table->unsignedInteger('id_solicitud');
            $table->string('tipo_documento', 80);
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->unsignedInteger('tamano_kb');
            $table->timestamp('fecha_subida')->useCurrent();

            $table->foreign('id_solicitud', 'fk_documento_solicitud')
                ->references('id_solicitud')
                ->on('bienestar.solicitud_apoyo')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('id_solicitud', 'idx_documento_solicitud');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.documento_adjunto');
    }
};
