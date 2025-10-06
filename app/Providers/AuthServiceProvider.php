<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // \App\Models\Cliente::class => \App\Policies\ClientePolicy::class,
        \App\Models\Proyecto::class => \App\Policies\ProyectoPolicy::class,
        // \App\Models\Programa::class => \App\Policies\ProgramaPolicy::class,
        // \App\Models\Fase::class => \App\Policies\FasePolicy::class,
        // \App\Models\Dashboard::class => \App\Policies\DashboardPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
