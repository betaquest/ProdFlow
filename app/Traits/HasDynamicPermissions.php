<?php

namespace App\Traits;

use App\Models\User;

trait HasDynamicPermissions
{
    /**
     * Nombre del recurso
     * Debe ser definido en la Policy que use este trait
     * Ejemplo: protected string $resource = 'clientes';
     */

    /**
     * Verifica si el usuario puede realizar una acción en base a permisos dinámicos
     */
    protected function checkPermission(User $user, string $action): bool
    {
        // El administrador siempre tiene acceso
        if ($user->hasRole('Administrador')) {
            return true;
        }

        // Verificar si el usuario tiene el permiso específico
        $permission = "{$this->resource}.{$action}";

        // Primero verificar permisos directos del usuario
        if ($user->hasPermissionTo($permission)) {
            return true;
        }

        // Si el usuario tiene área, verificar permisos del área
        if ($user->area_id && $user->area) {
            return $user->area->hasPermission($permission);
        }

        return false;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, $model): bool
    {
        return $this->checkPermission($user, 'ver');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $model): bool
    {
        return $this->checkPermission($user, 'editar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $model): bool
    {
        return $this->checkPermission($user, 'eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, $model): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, $model): bool
    {
        return $user->hasRole('Administrador');
    }
}
