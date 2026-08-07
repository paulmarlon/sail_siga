<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('matriculacion_materias', function (Blueprint $table) {
            $table->id();

            // Relaciones académicas
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('oferta_id')->constrained('oferta_academicas')->onDelete('cascade');
            $table->foreignId('estado_id')->constrained('estados');

            $table->timestamp('fecha_registro')->useCurrent();

            // SoftDeletes para mantener historial aunque se retire la materia
            $table->softDeletes();
            $table->timestamps();

            // Índice único compuesto para evitar duplicados en la misma oferta
            $table->unique(['estudiante_id', 'oferta_id'], 'unique_estudiante_oferta');

            // Índice adicional para búsquedas rápidas de materias por estudiante
            $table->index('estudiante_id');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculacion_materias');
    }
};
