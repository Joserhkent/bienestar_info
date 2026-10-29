<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cambiamos a TEXT las columnas sensibles para garantizar espacio suficiente
     * al almacenar payloads cifrados (AES-256-CBC / MAC de Laravel).
     */
    public function up(): void
    {
        Schema::table('bienestar.cita', function (Blueprint $table) {
            $table->text('motivo')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bienestar.cita', function (Blueprint $table) {
            $table->string('motivo', 255)->change();
        });
    }
};
