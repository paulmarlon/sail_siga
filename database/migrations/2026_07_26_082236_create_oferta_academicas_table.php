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
        Schema::create('oferta_academicas', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('periodo_id')->constrained('periodos')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('paralelo_id')->constrained('paralelos')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('turno_id')->constrained('turnos')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('pensum_id')->constrained('pensums')->onUpdate('cascade')->onDelete('restrict');

            // Campos operativos
            $table->integer('cupo_maximo')->default(80);

            // Estado (asumiendo que tu tabla de estados se llama 'estados')
            $table->foreignId('estado_id')->constrained('estados')->onUpdate('cascade')->onDelete('restrict');
            $table->softDeletes(); // <--- Agrega esta línea

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferta_academicas');
    }
};
