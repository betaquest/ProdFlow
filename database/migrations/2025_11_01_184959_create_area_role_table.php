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
        Schema::create('area_role', function (Blueprint $table) {
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');

            $table->primary(['area_id', 'role_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_role');
    }
};
