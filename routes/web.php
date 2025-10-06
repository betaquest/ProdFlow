<?php

use App\Livewire\DashboardView;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboards/{dashboard:slug}', DashboardView::class)
    ->name('dashboards.show');
