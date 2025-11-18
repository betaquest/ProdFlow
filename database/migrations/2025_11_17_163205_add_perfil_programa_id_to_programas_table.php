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
        Schema::table('programas', function (Blueprint $table) {
            $table->foreignId('perfil_programa_id')
                ->nullable()
                ->after('proyecto_id')
                ->constrained('perfiles_programa')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->dropForeign(['perfil_programa_id']);
            $table->dropColumn('perfil_programa_id');
        });
    }
};
