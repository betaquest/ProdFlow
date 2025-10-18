<?php

namespace App\Policies;

use App\Models\Programa;
use App\Models\User;
use App\Traits\HasDynamicPermissions;
use Illuminate\Auth\Access\Response;

class ProgramaPolicy
{
    use HasDynamicPermissions;

    protected string $resource = 'programas';

    /**
     * Determine whether the user can view reports.
     */
    public function viewReports(User $user): bool
    {
        return $this->checkPermission($user, 'ver_reportes');
    }
}
