<?php

namespace App\Observers;

use App\Models\Proyecto;
use Illuminate\Support\Facades\Cache;

class ProyectoObserver
{
    /**
     * Handle the Proyecto "updated" event.
     */
    public function updated(Proyecto $proyecto): void
    {
        Cache::forget('dashboard_stats');
    }

    /**
     * Handle the Proyecto "deleted" event.
     */
    public function deleted(Proyecto $proyecto): void
    {
        Cache::forget('dashboard_stats');
    }
}
