<?php

namespace App\Providers;

use App\Models\AvanceFase;
use App\Models\Programa;
use App\Observers\AvanceFaseObserver;
use App\Observers\ProgramaObserver;
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
        AvanceFase::observe(AvanceFaseObserver::class);
        Programa::observe(ProgramaObserver::class);
    }
}
