<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inscripcion_carreras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('carrera_id')->constrained('carreras')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->date('fecha_inscripcion')->default(DB::raw('CURRENT_DATE'));
            $table->boolean('es_especialidad_activa')->default(true);
            $table->foreignId('registrado_por_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('estado_id')->constrained('estados')->onDelete('restrict');
            $table->timestamps();

            $table->unique(['estudiante_id', 'carrera_id', 'periodo_id'], 'uk_estudiante_carrera_periodo');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcion_carreras');
    }
};
