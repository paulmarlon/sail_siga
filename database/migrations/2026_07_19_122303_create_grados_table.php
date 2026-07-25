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
        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('orden'); // Para saber si es 1ero, 2do, etc.
            $table->integer('ciclo')->default(1); // 1: Tronco Común, 2: Especialidad

            // Relaciones
            $table->foreignId('nivel_id')->constrained('nivels');
            $table->foreignId('estado_id')->constrained('estados');

            $table->timestamps();
            $table->softDeletes(); // ¡Muy importante para tu lógica de papelera!
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grados');
    }
};
