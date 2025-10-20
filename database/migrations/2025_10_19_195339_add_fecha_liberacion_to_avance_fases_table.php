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
            $table->timestamp('fecha_liberacion')->nullable()->after('fecha_fin')
                ->comment('Fecha en que se liberó/notificó esta fase desde la fase anterior');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avance_fases', function (Blueprint $table) {
            $table->dropColumn('fecha_liberacion');
        });
    }
};
