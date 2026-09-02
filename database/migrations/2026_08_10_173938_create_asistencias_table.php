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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('matriculacion_id')->constrained('matriculacion_materias')->cascadeOnDelete();

            // Campos principales
            $table->date('fecha');
            $table->foreignId('estado_id')->constrained('estados');
            $table->string('observacion')->nullable();

            $table->timestamps();

            // --- ÍNDICES DE RENDIMIENTO Y REGLA DE NEGOCIO ---
            // 1. Evita doble registro de asistencia para el mismo estudiante en la misma fecha
            $table->unique(['matriculacion_id', 'fecha']);

            // 2. Índice individual para búsquedas rápidas por fecha (muy útil en reportes y vistas diarias)
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
