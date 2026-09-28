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
        Schema::create('configuracion_componente_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuracion_parcial_id')->constrained('configuracion_parcial_meta')->cascadeOnDelete();
            $table->string('tipo_componente'); // 'examen', 'trabajo_practico', etc.
            $table->decimal('ponderacion_componente', 5, 2);
            $table->boolean('es_obligatorio')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_componente_meta');
    }
};
