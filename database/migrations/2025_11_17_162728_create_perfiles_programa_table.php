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
        Schema::create('perfiles_programa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique(); // 'In-House', 'Walk-In', etc.
            $table->text('descripcion')->nullable();
            $table->json('configuracion'); // {fases: [{fase_id, area_id, orden, requiere_aprobacion}]}
            $table->boolean('activo')->default(true);
            $table->boolean('predeterminado')->default(false); // Solo uno puede ser true
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles_programa');
    }
};
