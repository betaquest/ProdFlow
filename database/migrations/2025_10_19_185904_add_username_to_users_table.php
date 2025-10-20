<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name')->nullable();

            // Verificar si la columna active ya existe
            if (!Schema::hasColumn('users', 'active')) {
                $table->boolean('active')->default(true)->after('password');
            }
        });

        // Generar usernames para usuarios existentes basados en el email
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $username = explode('@', $user->email)[0];
            $baseUsername = $username;
            $counter = 1;

            // Asegurar que el username sea único
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        // Hacer el username obligatorio después de generar los valores
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');

            // Solo eliminar active si fue creada por esta migración
            // No la eliminamos para evitar problemas si ya existía
        });
    }
};
