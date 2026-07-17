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
        Schema::create('configuracions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_institucion');
            $table->string('sigla_institucion', 20);
            $table->string('nit', 50)->unique();
            $table->string('telefono')->nullable();
            $table->string('email_contacto')->nullable();
            $table->string('web')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('divisa', 10)->default('BOB');
            $table->foreignId('domicilio_id')->nullable()->unique()->constrained('domicilios');

            // Define la relación aquí UNA sola vez
            $table->foreignId('gestion_actual_id')
                ->nullable()
                ->constrained('gestions')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracions');
    }
};
