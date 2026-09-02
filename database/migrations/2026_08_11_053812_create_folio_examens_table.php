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
        Schema::create('folio_examens', function (Blueprint $table) {
            $table->id();

            // Relaciones foráneas
            $table->foreignId('programacion_id')->constrained('programacion_examens')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();

            // Campo unificado y limpio para el código del folio
            $table->string('codigo_folio')->comment('Código único impreso en el examen (Ej: FOLIO-017 o TEMP-17)');

            // Ciclo de vida y calificación
            $table->string('estado_folio')->default('Generado')->comment('Generado, Foliado_Y_Separado, Calificado, Consolidado');
            $table->decimal('nota', 5, 2)->nullable()->comment('Nota ingresada por el docente');
            $table->string('observacion')->nullable()->comment('Ej: Sin taco, Anulado, Copiando');

            $table->timestamps();
            $table->softDeletes();

            // Índices y restricciones de unicidad
            $table->index('codigo_folio');

            // Un código de folio no puede repetirse dentro de la misma programación de examen
            $table->unique(['programacion_id', 'codigo_folio'], 'folio_prog_codigo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folio_examens');
    }
};
