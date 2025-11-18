<?php

namespace App\Observers;

use App\Models\Programa;
use App\Models\Fase;
use App\Models\AvanceFase;
use Illuminate\Support\Facades\Auth;

class ProgramaObserver
{
    /**
     * Handle the Programa "created" event.
     * Crea automáticamente el avance de la primera fase y asigna al responsable seleccionado
     */
    public function created(Programa $programa): void
    {
        $primeraFaseData = null;
        $perfilId = $programa->perfil_programa_id;

        // Si no tiene perfil asignado, usar el predeterminado
        if (!$perfilId) {
            $perfilPredeterminado = \App\Models\PerfilPrograma::predeterminado()->first();
            if ($perfilPredeterminado) {
                $perfilId = $perfilPredeterminado->id;
                // Actualizar el programa con el perfil predeterminado
                $programa->update(['perfil_programa_id' => $perfilId]);
            }
        }

        // Opción 1: Si tiene perfil asignado o se asignó el predeterminado
        if ($perfilId) {
            $perfil = \App\Models\PerfilPrograma::find($perfilId);
            if ($perfil) {
                $primeraFaseData = $perfil->getPrimeraFase();
            }
        }

        // Opción 2: Fallback si no hay perfiles configurados
        if (!$primeraFaseData) {
            $primeraFase = Fase::where('activo', true)->orderBy('orden', 'asc')->first();
            if ($primeraFase) {
                $primeraFaseData = [
                    'fase' => $primeraFase,
                    'area_id' => $primeraFase->determinarArea(),
                ];
            }
        }

        if ($primeraFaseData && isset($primeraFaseData['fase'])) {
            $primeraFase = $primeraFaseData['fase'];

            // Usar el área del perfil si existe, sino usar el método inteligente de la fase
            $areaId = $primeraFaseData['area_id'] ?? $primeraFase->determinarArea();

            // Determinar el responsable: usar el seleccionado o buscar por el área de la primera fase
            $responsableId = $programa->responsable_inicial_id;

            if (!$responsableId) {
                // Si no hay responsable seleccionado, buscar usuario en el área de la primera fase
                if ($areaId) {
                    $areaUser = \App\Models\User::where('area_id', $areaId)->first();
                    $responsableId = $areaUser?->id;
                }

                // Fallback: buscar usuario con rol Ingenieria o usuario actual
                if (!$responsableId) {
                    $ingenieriaUser = \App\Models\User::role('Ingenieria')->first();
                    $responsableId = $ingenieriaUser?->id ?? Auth::id();
                }
            }

            // Crear avance de fase automáticamente
            AvanceFase::create([
                'programa_id' => $programa->id,
                'fase_id' => $primeraFase->id,
                'responsable_id' => $responsableId,
                'area_id' => $areaId,
                'estado' => 'pending',
                'activo' => true,
            ]);
        }
    }

    /**
     * Handle the Programa "updated" event.
     */
    public function updated(Programa $programa): void
    {
        //
    }

    /**
     * Handle the Programa "deleted" event.
     */
    public function deleted(Programa $programa): void
    {
        //
    }

    /**
     * Handle the Programa "restored" event.
     */
    public function restored(Programa $programa): void
    {
        //
    }

    /**
     * Handle the Programa "force deleted" event.
     */
    public function forceDeleted(Programa $programa): void
    {
        //
    }
}
