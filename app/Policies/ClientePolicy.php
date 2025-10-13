<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use App\Traits\HasDynamicPermissions;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{
    use HasDynamicPermissions;

    protected string $resource = 'clientes';
}
