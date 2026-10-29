<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.estudiante_tipo_condicion', function (Blueprint $table) {
            $table->unsignedInteger('id_estudiante');
            $table->unsignedInteger('id_tipo_condicion');
            $table->string('grado', 20);
            $table->boolean('requiere_aula_accesible')->default(false);
            $table->text('pauta_pedagogica')->nullable();
            $table->string('certificado_conadis', 50)->nullable();
            $table->boolean('visible_docente')->default(false);
            $table->timestamp('fecha_registro')->useCurrent();

            $table->primary(['id_estudiante', 'id_tipo_condicion']);

            $table->foreign('id_estudiante', 'fk_pivot_condicion_estudiante')
                ->references('id_estudiante')
                ->on('bienestar.estudiante')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_tipo_condicion', 'fk_pivot_condicion_tipo')
                ->references('id_tipo_condicion')
                ->on('bienestar.tipo_condicion')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('requiere_aula_accesible', 'idx_pivot_condicion_accesible');
            $table->index('visible_docente', 'idx_pivot_condicion_docente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.estudiante_tipo_condicion');
    }
};
