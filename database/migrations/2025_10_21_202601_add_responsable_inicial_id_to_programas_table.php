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
            $table->foreignId('responsable_inicial_id')->nullable()->after('proyecto_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->dropForeign(['responsable_inicial_id']);
            $table->dropColumn('responsable_inicial_id');
        });
    }
};
