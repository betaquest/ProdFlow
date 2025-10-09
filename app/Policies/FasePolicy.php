<?php

namespace App\Policies;

use App\Models\Fase;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Solo Administradores pueden ver las fases
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Fase $fase): bool
    {
        return $user->hasRole('Administrador');
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
    public function update(User $user, Fase $fase): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Fase $fase): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Fase $fase): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Fase $fase): bool
    {
        return $user->hasRole('Administrador');
    }
}
