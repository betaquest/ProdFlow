<?php

namespace App\Providers;

use App\Models\AvanceFase;
use App\Models\Programa;
use App\Models\Proyecto;
use App\Models\Cliente;
use App\Observers\AvanceFaseObserver;
use App\Observers\ProgramaObserver;
use App\Observers\ProyectoObserver;
use App\Observers\ClienteObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observadores para cache invalidation automática
        AvanceFase::observe(AvanceFaseObserver::class);
        Programa::observe(ProgramaObserver::class);
        Proyecto::observe(ProyectoObserver::class);
        Cliente::observe(ClienteObserver::class);
    }
}
