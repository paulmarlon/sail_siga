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
        Schema::create('estudiante_ppff', function (Blueprint $table) {
            $table->id();

            // Relación con estudiantes
            $table->foreignId('estudiante_id')
                ->constrained('estudiantes')
                ->onDelete('cascade');

            // Relación directa con personas (el apoderado es una persona)
            $table->foreignId('ppff_persona_id')
                ->constrained('personas')
                ->onDelete('cascade');

            $table->string('parentesco')->default('Apoderado');
            $table->boolean('es_tutor_principal')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante_ppff');
    }
};
