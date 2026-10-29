<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.reporte', function (Blueprint $table) {
            $table->increments('id_reporte');
            $table->unsignedInteger('id_usuario');
            $table->string('tipo_reporte', 50);
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->string('formato', 10);
            $table->timestamp('fecha_generacion')->useCurrent();

            $table->foreign('id_usuario', 'fk_reporte_usuario')
                ->references('id_usuario')
                ->on('bienestar.usuario')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('id_usuario', 'idx_reporte_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.reporte');
    }
};
