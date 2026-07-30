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
        Schema::create('oferta_docente_historials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_id')->constrained('oferta_academicas')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('personals')->onDelete('cascade');
            $table->string('contrato_id')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('motivo_cambio')->nullable();
            $table->foreignId('estado_id')->constrained('estados');
            $table->foreignId('registrado_por_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferta_docente_historials');
    }
};
