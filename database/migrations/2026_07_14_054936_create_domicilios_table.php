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
        Schema::create('domicilios', function (Blueprint $table) {
            $table->id();
            $table->string('pais')->default('Bolivia');
            $table->string('departamento')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('zona')->nullable();
            $table->string('avenida')->nullable();
            $table->string('numero')->nullable();
            $table->text('referencia')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->enum('tipo_domicilio', ['RESIDENCIA', 'TRABAJO', 'REFERENCIA'])->default('RESIDENCIA');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domicilios');
    }
};
