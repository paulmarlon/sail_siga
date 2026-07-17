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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('ci', 20)->unique();
            $table->string('nombres');
            $table->string('ap_paterno')->nullable();
            $table->string('ap_materno')->nullable();
            $table->date('fecha_nacimiento');
            $table->char('sexo', 1);
            $table->string('celular', 20)->nullable();
            $table->string('email_personal')->nullable();
            $table->string('foto_path')->nullable();
            $table->foreignId('domicilio_id')->nullable()->unique()->constrained('domicilios')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
