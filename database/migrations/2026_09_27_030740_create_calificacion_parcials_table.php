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

            $table->integer('nro_parcial')->comment('1 (Pondera 30%), 2 (Pondera 30%), o 3 (Pondera 40%)');
            $table->decimal('ponderacion_parcial', 5, 2);

            // Componentes estándar sobre 100
            $table->decimal('tp_nota', 5, 2)->default(0)->comment('Trabajo práctico evaluado sobre 100');
            $table->decimal('exam_nota', 5, 2)->default(0)->comment('Examen evaluado sobre 100');

            // Trazabilidad de examen
            $table->string('metodo_registro')->default('directa')->comment('directa, folio, dictada');

            $table->foreignId('folio_id')
                ->nullable()
                ->constrained('folio_examens')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->decimal('nota_parcial_calculada', 5, 2)->nullable();

            $table->foreignId('registrado_por_user_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreignId('estado_id')
                ->nullable()
                ->constrained('estados')
                ->onUpdate('cascade')
                ->onDelete('set null');

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
