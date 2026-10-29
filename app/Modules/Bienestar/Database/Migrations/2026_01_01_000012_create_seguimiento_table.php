<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.seguimiento', function (Blueprint $table) {
            $table->increments('id_seguimiento');
            $table->unsignedInteger('id_derivacion');
            $table->timestamp('fecha_sesion')->useCurrent();
            $table->text('nota_avance');
            $table->text('acuerdos')->nullable();
            $table->timestamp('proxima_fecha')->nullable();

            $table->foreign('id_derivacion', 'fk_seguimiento_derivacion')
                ->references('id_derivacion')
                ->on('bienestar.derivacion')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('id_derivacion', 'idx_seguimiento_derivacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.seguimiento');
    }
};
