<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerfilPrograma;
use App\Models\Fase;
use App\Models\Area;

class PerfilProgramaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de fases y áreas (ajustar según tu configuración real)
        // NOTA: Este seeder asume que ya existen las fases y áreas en la BD

        // Perfil 1: In-House (Predeterminado)
        // Este perfil omite Ingeniería y tiene menos fases
        $inHouseConfig = [
            'fases' => []
        ];

        // Buscar las fases por nombre y construir la configuración
        $fasesInHouse = [
            ['nombre' => 'Captura', 'area_nombre' => 'Captura', 'orden' => 1, 'requiere_aprobacion' => false],
            ['nombre' => 'Abastecimiento', 'area_nombre' => 'Abastecimiento', 'orden' => 2, 'requiere_aprobacion' => true],
            ['nombre' => 'Corte', 'area_nombre' => 'Corte', 'orden' => 3, 'requiere_aprobacion' => true],
            ['nombre' => 'Enchape', 'area_nombre' => 'Ensamblado', 'orden' => 4, 'requiere_aprobacion' => true],
            ['nombre' => 'Armado', 'area_nombre' => 'Armado', 'orden' => 5, 'requiere_aprobacion' => true],
            ['nombre' => 'Instalacion', 'area_nombre' => 'Instalacion', 'orden' => 6, 'requiere_aprobacion' => true],
            ['nombre' => 'Completado', 'area_nombre' => 'Instalacion', 'orden' => 7, 'requiere_aprobacion' => true],
        ];

        foreach ($fasesInHouse as $faseData) {
            $fase = Fase::where('nombre', $faseData['nombre'])->first();
            $area = Area::where('nombre', $faseData['area_nombre'])->first();

            if ($fase && $area) {
                $inHouseConfig['fases'][] = [
                    'fase_id' => $fase->id,
                    'area_id' => $area->id,
                    'orden' => $faseData['orden'],
                    'requiere_aprobacion' => $faseData['requiere_aprobacion'],
                ];
            }
        }

        PerfilPrograma::create([
            'nombre' => 'In-House',
            'descripcion' => 'Perfil para programas internos. Inicia en Captura (sin Ingeniería) y usa áreas simplificadas.',
            'configuracion' => $inHouseConfig,
            'activo' => true,
            'predeterminado' => true, // In-House es el predeterminado
        ]);

        // Perfil 2: Walk-In (Tradicional)
        // Este perfil incluye todas las fases en orden tradicional
        $walkInConfig = [
            'fases' => []
        ];

        $fasesWalkIn = [
            ['nombre' => 'Ingenieria', 'area_nombre' => 'Ingenieria', 'orden' => 1, 'requiere_aprobacion' => false],
            ['nombre' => 'Captura', 'area_nombre' => 'Captura', 'orden' => 2, 'requiere_aprobacion' => true],
            ['nombre' => 'Abastecimiento', 'area_nombre' => 'Abastecimiento', 'orden' => 3, 'requiere_aprobacion' => true],
            ['nombre' => 'Corte', 'area_nombre' => 'Corte', 'orden' => 4, 'requiere_aprobacion' => true],
            ['nombre' => 'Enchape', 'area_nombre' => 'Ensamblado', 'orden' => 5, 'requiere_aprobacion' => true],
            ['nombre' => 'Entrega', 'area_nombre' => 'Armado', 'orden' => 6, 'requiere_aprobacion' => true],
            ['nombre' => 'Armado', 'area_nombre' => 'Armado', 'orden' => 7, 'requiere_aprobacion' => true],
            ['nombre' => 'Instalacion', 'area_nombre' => 'Instalacion', 'orden' => 8, 'requiere_aprobacion' => true],
            ['nombre' => 'Completado', 'area_nombre' => 'Instalacion', 'orden' => 9, 'requiere_aprobacion' => true],
        ];

        foreach ($fasesWalkIn as $faseData) {
            $fase = Fase::where('nombre', $faseData['nombre'])->first();
            $area = Area::where('nombre', $faseData['area_nombre'])->first();

            if ($fase && $area) {
                $walkInConfig['fases'][] = [
                    'fase_id' => $fase->id,
                    'area_id' => $area->id,
                    'orden' => $faseData['orden'],
                    'requiere_aprobacion' => $faseData['requiere_aprobacion'],
                ];
            }
        }

        PerfilPrograma::create([
            'nombre' => 'Walk-In',
            'descripcion' => 'Perfil tradicional completo. Inicia en Ingeniería e incluye todas las fases del proceso.',
            'configuracion' => $walkInConfig,
            'activo' => true,
            'predeterminado' => false,
        ]);

        $this->command->info('Perfiles de programa creados exitosamente.');
        $this->command->info('- In-House (Predeterminado): ' . count($inHouseConfig['fases']) . ' fases');
        $this->command->info('- Walk-In: ' . count($walkInConfig['fases']) . ' fases');
    }
}
