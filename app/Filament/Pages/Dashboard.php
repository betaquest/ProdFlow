<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        // Solo el Administrador puede ver el Dashboard
        return Auth::user()->hasRole('Administrador');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Solo mostrar en el menú si es Administrador
        return Auth::user()->hasRole('Administrador');
    }
}
