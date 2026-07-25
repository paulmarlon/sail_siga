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
        Schema::create('pensums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras');
            $table->foreignId('materia_id')->constrained('materias');
            $table->foreignId('grado_id')->constrained('grados'); // Ej: 1er semestre, 2do semestre
            $table->boolean('es_obligatoria')->default(true);
            $table->foreignId('estado_id')->constrained('estados');

            $table->timestamps();
            $table->softDeletes(); // Buena práctica para recuperar borradores accidentales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pensums');
    }
};
