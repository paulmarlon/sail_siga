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
        Schema::create('configuracion_parcial_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->nullOnDelete();
            $table->integer('nro_parcial');
            $table->string('nombre');
            $table->decimal('ponderacion_parcial', 5, 2);
            $table->foreignId('estado_id')->constrained('estados');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_parcial_meta');
    }
};
