<?php

namespace App\Observers;

use App\Models\Cliente;
use Illuminate\Support\Facades\Cache;

class ClienteObserver
{
    /**
     * Handle the Cliente "updated" event.
     */
    public function updated(Cliente $cliente): void
    {
        Cache::forget('dashboard_stats');
    }

    /**
     * Handle the Cliente "deleted" event.
     */
    public function deleted(Cliente $cliente): void
    {
        Cache::forget('dashboard_stats');
    }
}
