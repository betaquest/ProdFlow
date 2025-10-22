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
        // Obtener la primera fase (orden = 1 o la menor)
        $primeraFase = Fase::orderBy('orden', 'asc')->first();

        if ($primeraFase) {
            // Determinar el responsable: usar el seleccionado o el primer usuario de Ingeniería
            $responsableId = $programa->responsable_inicial_id;

            if (!$responsableId) {
                // Si no hay responsable seleccionado, buscar el primer usuario con rol Ingenieria
                $ingenieriaUser = \App\Models\User::role('Ingenieria')->first();
                $responsableId = $ingenieriaUser?->id ?? Auth::id();
            }

            // Crear avance de fase automáticamente
            AvanceFase::create([
                'programa_id' => $programa->id,
                'fase_id' => $primeraFase->id,
                'responsable_id' => $responsableId,
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
