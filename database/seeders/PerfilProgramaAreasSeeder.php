<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerfilPrograma;
use App\Models\Area;

class PerfilProgramaAreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔧 Asignando áreas a perfiles de programa...');

        // Obtener las áreas
        $ingenieria = Area::where('nombre', 'Ingenieria')->first();
        $captura = Area::where('nombre', 'Captura')->first();

        if (!$ingenieria || !$captura) {
            $this->command->error('❌ No se encontraron las áreas Ingenieria o Captura');
            return;
        }

        // Asignar área de Ingeniería al perfil In-House
        $inHouse = PerfilPrograma::where('nombre', 'In-House')->first();
        if ($inHouse) {
            // Sincronizar áreas (esto limpia las áreas existentes y asigna solo la nueva)
            $inHouse->areas()->sync([$ingenieria->id]);
            $this->command->info("✓ Perfil 'In-House' asignado al área 'Ingenieria'");
        }

        // Asignar área de Captura al perfil Walk-In
        $walkIn = PerfilPrograma::where('nombre', 'Walk-In')->first();
        if ($walkIn) {
            $walkIn->areas()->sync([$captura->id]);
            $this->command->info("✓ Perfil 'Walk-In' asignado al área 'Captura'");
        }

        // Si hay más perfiles, limpiar sus áreas (disponibles para todos)
        $otrosPerfiles = PerfilPrograma::whereNotIn('nombre', ['In-House', 'Walk-In'])->get();
        foreach ($otrosPerfiles as $perfil) {
            $perfil->areas()->sync([]);
            $this->command->info("✓ Perfil '{$perfil->nombre}' disponible para todas las áreas");
        }

        $this->command->info('✅ Áreas de perfiles actualizadas correctamente');
    }
}
