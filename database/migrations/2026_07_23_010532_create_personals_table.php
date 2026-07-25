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
        Schema::create('personals', function (Blueprint $table) {
            $table->id();
            // Vínculo obligatorio a la persona biográfica (una persona es personal)
            $table->foreignId('persona_id')->unique()->constrained('personas')->onDelete('cascade');

            // Opcional: solo si el empleado o docente tiene credenciales de acceso al sistema
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('users')->onDelete('set null');

            // Tipo de personal: 'docente', 'administrativo', etc.
            $table->string('tipo', 50);

            // Profesión u oficio
            $table->string('profesion', 100)->nullable();

            // Estado del personal (activo, de baja, licencia, etc.)
            $table->foreignId('estado_id')->constrained('estados');

            $table->timestamps();
            $table->softDeletes(); // Buena práctica para conservar historial laboral
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personals');
    }
};
