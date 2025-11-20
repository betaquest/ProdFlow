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
        Schema::table('avance_fases', function (Blueprint $table) {
            // Eliminar la foreign key actual
            $table->dropForeign(['responsable_id']);

            // Recrear la foreign key con nullOnDelete
            $table->foreign('responsable_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avance_fases', function (Blueprint $table) {
            // Eliminar la foreign key con nullOnDelete
            $table->dropForeign(['responsable_id']);

            // Recrear la foreign key sin nullOnDelete (comportamiento original)
            $table->foreign('responsable_id')
                ->references('id')
                ->on('users');
        });
    }
};
