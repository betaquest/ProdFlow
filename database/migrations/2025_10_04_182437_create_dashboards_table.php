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
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                   // Nombre del dashboard
            $table->string('slug')->unique();           // Parte final de la URL (ej: "cocina", "general")
            $table->json('criterios')->nullable();      // Filtros o condiciones personalizadas (por cliente, proyecto, etc.)
            $table->text('descripcion')->nullable();
            $table->text('notas')->nullable();    // Descripción o notas
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};
