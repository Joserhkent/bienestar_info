<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienestar.deporte', function (Blueprint $table) {
            $table->increments('id_deporte');
            $table->string('nombre', 80)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienestar.deporte');
    }
};
