<?php

namespace App\Observers;

use App\Models\AvanceFase;
use App\Models\User;
use App\Notifications\AvanceFaseActualizado;
use Filament\Notifications\Notification;

class AvanceFaseObserver
{
    /**
     * Handle the AvanceFase "created" event.
     */
    public function created(AvanceFase $avanceFase): void
    {
        // Notificar al responsable cuando se crea un nuevo avance
        if ($avanceFase->responsable) {
            Notification::make()
                ->title('Nuevo Avance Asignado')
                ->body("Se te ha asignado un nuevo avance en {$avanceFase->programa->nombre}")
                ->success()
                ->sendToDatabase($avanceFase->responsable);
        }
    }

    /**
     * Handle the AvanceFase "updated" event.
     */
    public function updated(AvanceFase $avanceFase): void
    {
        // Verificar si cambió el estado
        if ($avanceFase->isDirty('estado')) {
            $estadoAnterior = $avanceFase->getOriginal('estado');

            // Notificar al responsable
            if ($avanceFase->responsable) {
                $avanceFase->responsable->notify(
                    new AvanceFaseActualizado($avanceFase, $estadoAnterior)
                );
            }

            // Notificación Filament para admins
            $admins = User::role('Administrador')->get();
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Cambio de Estado en Avance')
                    ->body("El avance de {$avanceFase->programa->nombre} cambió de '{$estadoAnterior}' a '{$avanceFase->estado}'")
                    ->info()
                    ->sendToDatabase($admin);
            }
        }
    }

    /**
     * Handle the AvanceFase "deleted" event.
     */
    public function deleted(AvanceFase $avanceFase): void
    {
        //
    }

    /**
     * Handle the AvanceFase "restored" event.
     */
    public function restored(AvanceFase $avanceFase): void
    {
        //
    }

    /**
     * Handle the AvanceFase "force deleted" event.
     */
    public function forceDeleted(AvanceFase $avanceFase): void
    {
        //
    }
}
