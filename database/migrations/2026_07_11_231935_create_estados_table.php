<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {
        $table->id();
        $table->string('nombre')->unique();
        $table->string('slug')->unique();
        $table->string('contexto')->default('global');
        $table->boolean('permite_login')->default(true);
        $table->boolean('permite_procesos_academicos')->default(true);
        $table->string('color_hex')->nullable();

        // El índice compuesto para evitar duplicados en el mismo contexto
        $table->unique(['slug', 'contexto']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
