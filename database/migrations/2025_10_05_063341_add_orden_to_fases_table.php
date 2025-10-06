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
        Schema::table('fases', function (Blueprint $table) {
            $table->integer('orden')->default(0)->after('nombre');
            $table->boolean('requiere_aprobacion')->default(true)->after('orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fases', function (Blueprint $table) {
            $table->dropColumn(['orden', 'requiere_aprobacion']);
        });
    }
};
