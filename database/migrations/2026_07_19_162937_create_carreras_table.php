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
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            // Permite que una carrera sea derivada de otra (ej. una especialidad de un tronco común)
            $table->foreignId('carrera_base_id')->nullable()->constrained('carreras');
            $table->string('nombre');
            $table->string('sigla')->unique();
            $table->string('resolucion')->nullable();
            $table->integer('duracion'); // O duración en años/semestres
            $table->string('titulo');
            $table->boolean('es_tronco_comun')->default(false);

            // Relaciones
            $table->foreignId('nivel_id')->constrained('nivels');
            $table->foreignId('estado_id')->constrained('estados');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};
