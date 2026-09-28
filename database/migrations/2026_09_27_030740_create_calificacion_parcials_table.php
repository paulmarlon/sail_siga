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
        Schema::create('calificacion_parcials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('matriculacion_id')
                ->constrained('matriculacion_materias')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Relación opcional con la configuración meta institucional de donde heredó
            $table->foreignId('configuracion_parcial_id')
                ->nullable()
                ->constrained('configuracion_parcial_meta')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->integer('nro_parcial')->comment('1, 2 o 3');
            $table->decimal('ponderacion_parcial', 5, 2)->comment('Peso oficial heredado de la meta (Ej: 30.00)');
            $table->decimal('nota_parcial_calculada', 5, 2)->nullable()->comment('Suma de los componentes ponderados del detalle');

            $table->foreignId('estado_id')
                ->nullable()
                ->constrained('estados')
                ->onUpdate('cascade')
                ->onDelete('set null')
                ->comment('Ej: Vigente, Bloqueado, Modificado');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacion_parcials');
    }
};
