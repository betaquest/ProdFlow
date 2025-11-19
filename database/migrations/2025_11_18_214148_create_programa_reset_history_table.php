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
        Schema::create('programa_reset_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained()->cascadeOnDelete();
            $table->string('programa_nombre');
            $table->foreignId('ejecutado_por')->constrained('users')->cascadeOnDelete();
            $table->json('datos_respaldo'); // Respaldo de avances eliminados
            $table->integer('total_avances_eliminados')->default(0);
            $table->text('motivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programa_reset_history');
    }
};
