<?php

use App\Livewire\DashboardView;
use App\Livewire\LandingPage;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('home');

Route::get('/dashboards/{dashboard:slug}', DashboardView::class)
    ->name('dashboards.show');
