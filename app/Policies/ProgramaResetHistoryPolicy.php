<?php

namespace App\Policies;

use App\Models\ProgramaResetHistory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProgramaResetHistoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('programas.ver_historial_reinicios');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProgramaResetHistory $programaResetHistory): bool
    {
        return $user->can('programas.ver_historial_reinicios');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // No se pueden crear manualmente
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProgramaResetHistory $programaResetHistory): bool
    {
        return false; // Solo lectura
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProgramaResetHistory $programaResetHistory): bool
    {
        return $user->can('programas.eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProgramaResetHistory $programaResetHistory): bool
    {
        return $user->can('programas.restaurar_avances');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProgramaResetHistory $programaResetHistory): bool
    {
        return $user->can('programas.eliminar');
    }
}
