<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Limpiar activity log cada domingo a las 2:00 AM
Schedule::command('app:clean-activity-log --days=90')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->timezone('America/Mexico_City')
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('✅ Activity log cleanup completed successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('❌ Activity log cleanup failed');
    });
