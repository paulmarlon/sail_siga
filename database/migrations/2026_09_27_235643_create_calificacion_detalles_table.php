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
        Schema::create('calificacion_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('calificacion_parcial_id')
                ->constrained('calificacion_parcials')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('tipo_componente')->comment("Valores: 'trabajo_practico', 'examen'");
            $table->decimal('ponderacion_componente', 5, 2)->comment('Porcentaje de peso de este componente (Ej: 25.00 o 75.00)');
            $table->decimal('nota', 5, 2)->comment('Nota obtenida evaluada sobre 100');

            $table->string('modalidad_origen')->default('directa')->comment("'directa', 'a_ciegas', 'dictada', 'extraordinaria'");

            $table->foreignId('folio_id')
                ->nullable()
                ->constrained('folio_examens')
                ->onUpdate('cascade')
                ->onDelete('set null')
                ->comment('Enlace al folio si se originó en examen a ciegas');

            $table->foreignId('estado_id')
                ->nullable()
                ->constrained('estados')
                ->onUpdate('cascade')
                ->onDelete('set null')
                ->comment('Estado del detalle (ej: vigente, anulado)');

            $table->foreignId('registrado_por_user_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null')
                ->comment('Usuario que ingresó o sincronizó este componente');

            $table->text('observacion')->nullable()->comment('Justificación médica, permiso o nota aclaratoria');

            $table->timestamps();

            // Índice compuesto para acelerar búsquedas masivas de libretas de curso
            $table->index(['calificacion_parcial_id', 'tipo_componente'], 'idx_calificacion_detalle_busqueda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacion_detalles');
    }
};
