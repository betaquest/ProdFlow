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
            $table->foreignId('creado_por')->nullable()->after('activo')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        Schema::table('avance_fases', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->dropColumn('creado_por');
            $table->dropSoftDeletes();
        });

        Schema::table('avance_fases', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
