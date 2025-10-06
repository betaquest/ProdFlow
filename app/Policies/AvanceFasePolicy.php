<?php

namespace App\Policies;

use App\Models\AvanceFase;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AvanceFasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('fases.ver') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AvanceFase $avanceFase): bool
    {
        // El usuario puede ver si es el responsable o tiene permiso general
        return $avanceFase->responsable_id === $user->id
            || $user->hasPermissionTo('fases.ver')
            || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AvanceFase $avanceFase): bool
    {
        // Solo el responsable o usuarios con permiso pueden actualizar
        return $avanceFase->responsable_id === $user->id
            || $user->hasPermissionTo('fases.editar')
            || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AvanceFase $avanceFase): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AvanceFase $avanceFase): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AvanceFase $avanceFase): bool
    {
        return $user->hasRole('Administrador');
    }
}
