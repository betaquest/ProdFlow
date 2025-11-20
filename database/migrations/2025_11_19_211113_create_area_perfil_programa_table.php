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
        Schema::create('area_perfil_programa', function (Blueprint $table) {
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('perfil_programa_id')->constrained('perfiles_programa')->cascadeOnDelete();
            $table->primary(['area_id', 'perfil_programa_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_perfil_programa');
    }
};
