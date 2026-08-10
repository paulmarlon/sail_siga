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
        Schema::create('programacion_examens', function (Blueprint $table) {
            $table->id();

            // Relación con oferta académica
            $table->foreignId('oferta_id')
                ->constrained('oferta_academicas')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->string('instancia');

            // Modalidad: directa, a_ciegas, dictada
            $table->string('modalidad');

            $table->string('tipo_proceso')->default('Ordinario');
            $table->dateTime('fecha_programada');

            // Relación con personal (responsable / docente)
            $table->foreignId('responsable_id')
                ->constrained('personals')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->boolean('bloqueado')->default(false);
            $table->text('observaciones_legales')->nullable();

            $table->softDeletes(); // <--- ESTO FALTA PARA LA PAPELERA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programacion_examens');
    }
};
