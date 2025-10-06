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
        Schema::create('avance_fases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users');
            $table->enum('estado', ['pending', 'progress', 'done'])->default('pending');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avance_fases');
    }
};
