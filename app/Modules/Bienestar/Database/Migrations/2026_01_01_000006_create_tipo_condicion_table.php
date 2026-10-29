<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.tipo_condicion', function (Blueprint $table) {
            $table->increments('id_tipo_condicion');
            $table->string('nombre', 100)->unique();
            $table->string('categoria', 50);
            $table->string('base_legal', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.tipo_condicion');
    }
};
