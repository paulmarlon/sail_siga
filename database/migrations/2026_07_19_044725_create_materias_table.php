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
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('sigla')->unique();
            $table->string('nombre');
            $table->text('descripcion');
            $table->integer('horas_academicas');
            $table->enum('tipo_materia', ['Teorica', 'Practica', 'Teorica-Practica']);
            $table->boolean('es_comun')->default(false);

            // Relación
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
        Schema::dropIfExists('materias');
    }
};
