<?php

namespace App\Policies;

use App\Models\Proyecto;
use App\Models\User;

class ProyectoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('proyectos.ver') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Proyecto $proyecto): bool
    {
        return $user->hasPermissionTo('proyectos.ver') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('proyectos.crear') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Proyecto $proyecto): bool
    {
        return $user->hasPermissionTo('proyectos.editar') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Proyecto $proyecto): bool
    {
        return $user->hasPermissionTo('proyectos.eliminar') || $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Proyecto $proyecto): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Proyecto $proyecto): bool
    {
        return $user->hasRole('Administrador');
    }
}
