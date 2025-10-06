<?php

namespace App\Policies;

use App\Models\Programa;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProgramaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('programas.ver') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Programa $programa): bool
    {
        return $user->hasPermissionTo('programas.ver') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('programas.crear') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Programa $programa): bool
    {
        return $user->hasPermissionTo('programas.editar') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Programa $programa): bool
    {
        return $user->hasPermissionTo('programas.eliminar') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Programa $programa): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Programa $programa): bool
    {
        return $user->hasRole('Administrador');
    }
}
