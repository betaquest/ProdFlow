<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fase;
use App\Models\Area;
use App\Models\AvanceFase;

class AsignarAreasAFases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:asignar-areas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna áreas a las fases y actualiza avances existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ASIGNANDO ÁREAS A FASES Y AVANCES ===');
        $this->newLine();

        // PASO 1: Asignar áreas a fases
        $this->info('📋 PASO 1: Asignando áreas a fases...');
        $fases = Fase::all();
        $fasesActualizadas = 0;

        foreach ($fases as $fase) {
            // Si ya tiene área, saltar
            if ($fase->area_id) {
                $this->line("  ⏭️  {$fase->nombre} - Ya tiene área asignada");
                continue;
            }

            // Usar el método inteligente determinarArea()
            $areaId = $fase->determinarArea();

            if ($areaId) {
                $fase->update(['area_id' => $areaId]);
                $area = Area::find($areaId);
                $this->info("  ✅ {$fase->nombre} → Área: {$area->nombre}");
                $fasesActualizadas++;
            } else {
                $this->warn("  ⚠️  {$fase->nombre} - No se encontró área correspondiente");
            }
        }

        $this->newLine();
        $this->info("Fases actualizadas: {$fasesActualizadas}");
        $this->newLine();

        // PASO 2: Actualizar avances existentes sin área
        $this->info('🔄 PASO 2: Actualizando avances de fases sin área asignada...');
        $avancesSinArea = AvanceFase::whereNull('area_id')->get();
        $avancesActualizados = 0;

        foreach ($avancesSinArea as $avance) {
            // Recargar la fase para obtener el area_id actualizado
            $fase = $avance->fase()->first();

            if ($fase) {
                // Usar el método inteligente para determinar el área
                $areaId = $fase->determinarArea();

                if ($areaId) {
                    $avance->update(['area_id' => $areaId]);
                    $area = Area::find($areaId);
                    $this->line("  ✅ Avance: {$avance->programa->nombre} / {$fase->nombre} → Área: {$area->nombre}");
                    $avancesActualizados++;
                } else {
                    $this->warn("  ⚠️  Avance: {$avance->programa->nombre} / {$fase->nombre} - No se pudo determinar área");
                }
            }
        }

        $this->newLine();
        $this->info("✅ Proceso completado:");
        $this->info("   - Fases actualizadas: {$fasesActualizadas}");
        $this->info("   - Avances actualizados: {$avancesActualizados}");
    }
}
