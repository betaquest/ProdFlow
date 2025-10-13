<?php

namespace App\Policies;

use App\Models\Proyecto;
use App\Models\User;
use App\Traits\HasDynamicPermissions;

class ProyectoPolicy
{
    use HasDynamicPermissions;

    protected string $resource = 'proyectos';
}
