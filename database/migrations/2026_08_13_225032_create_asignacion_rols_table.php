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
        Schema::create('asignacion_rols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->onDelete('cascade');
            $table->unsignedBigInteger('role_id'); // Relación con roles de Spatie
            $table->unsignedBigInteger('entidad_id')->nullable(); // Contexto (ej: ID de Carrera)
            $table->string('entidad_type')->nullable(); // Contexto (ej: 'App\Models\Carrera')
            $table->foreignId('estado_id')->constrained('estados');
            $table->timestamps();

            // Llave foránea para Spatie (usualmente la tabla se llama 'roles')
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_rols');
    }
};
