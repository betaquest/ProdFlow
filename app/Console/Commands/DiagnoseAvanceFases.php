<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AvanceFase;
use App\Models\Area;
use App\Models\User;
use App\Models\Fase;

class DiagnoseAvanceFases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:avances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose AvanceFases configuration and area assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== DIAGNÓSTICO DE AVANCES DE FASES ===');
        $this->newLine();

        // 1. Listar todas las áreas
        $this->info('📁 ÁREAS DISPONIBLES:');
        $areas = Area::all();
        foreach ($areas as $area) {
            $usuariosCount = $area->users()->count();
            $this->line("  - {$area->nombre} (ID: {$area->id}) - {$usuariosCount} usuarios");
        }
        $this->newLine();

        // 2. Listar todas las fases y sus áreas
        $this->info('📋 FASES Y SUS ÁREAS ASIGNADAS:');
        $fases = Fase::orderBy('orden')->get();
        foreach ($fases as $fase) {
            $areaInfo = $fase->area_id ? "Área: {$fase->area->nombre} (ID: {$fase->area_id})" : "❌ SIN ÁREA";
            $this->line("  - {$fase->nombre} (orden {$fase->orden}) - {$areaInfo}");
        }
        $this->newLine();

        // 3. Listar todos los avances y sus áreas
        $this->info('🔄 AVANCES DE FASES EXISTENTES:');
        $avances = AvanceFase::with(['programa', 'fase', 'area'])->get();

        if ($avances->isEmpty()) {
            $this->warn('  No hay avances de fases registrados');
        } else {
            foreach ($avances as $avance) {
                $programaNombre = $avance->programa->nombre;
                $faseNombre = $avance->fase->nombre;
                $areaInfo = $avance->area_id ? "Área: {$avance->area->nombre} (ID: {$avance->area_id})" : "❌ SIN ÁREA";
                $estado = $avance->estado;

                $this->line("  - Programa: {$programaNombre} | Fase: {$faseNombre} | {$areaInfo} | Estado: {$estado}");
            }
        }
        $this->newLine();

        // 4. Identificar avances sin área
        $avancesSinArea = AvanceFase::whereNull('area_id')->get();
        if ($avancesSinArea->isNotEmpty()) {
            $this->warn("⚠️  AVANCES SIN ÁREA ASIGNADA: {$avancesSinArea->count()}");
            foreach ($avancesSinArea as $avance) {
                $this->line("  - Programa: {$avance->programa->nombre} | Fase: {$avance->fase->nombre}");
            }
            $this->newLine();
        }

        // 5. Listar usuarios y sus áreas
        $this->info('👥 USUARIOS Y SUS ÁREAS:');
        $usuarios = User::all();
        foreach ($usuarios as $usuario) {
            $areaInfo = $usuario->area_id ? "Área: {$usuario->area->nombre} (ID: {$usuario->area_id})" : "❌ SIN ÁREA";
            $roles = $usuario->getRoleNames()->implode(', ');
            $this->line("  - {$usuario->name} - {$areaInfo} - Roles: {$roles}");
        }

        $this->newLine();
        $this->info('✅ Diagnóstico completado');
    }
}
