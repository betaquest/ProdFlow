<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReporteGeneralResource\Pages;
use Filament\Resources\Resource;

class ReporteGeneralResource extends Resource
{
    protected static ?string $navigationLabel = 'Reportes Generales';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 1;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ReporteGeneral::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        // return $user && $user->hasRole('Administrador');
        return $user && $user->hasAnyRole(['Administrador', 'Ingenieria']);

    }
}
