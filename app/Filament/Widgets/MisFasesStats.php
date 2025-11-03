<?php

namespace App\Filament\Widgets;

use App\Models\AvanceFase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MisFasesStats extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        // Filtrar por área del usuario (o todo si es administrador)
        $query = AvanceFase::query();
        if (!$user->hasRole('Administrador')) {
            $query->where('area_id', $user->area_id);
        }

        $totalFases = (clone $query)->count();
        $pendientes = (clone $query)->where('estado', 'pending')->count();
        $enProgreso = (clone $query)->where('estado', 'progress')->count();
        $completadas = (clone $query)->where('estado', 'done')->count();

        $descripcionTotal = $user->hasRole('Administrador')
            ? 'Fases totales en el sistema'
            : 'Fases de tu área';

        return [
            Stat::make('Total Asignadas', $totalFases)
                ->description($descripcionTotal)
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('info')
                ->chart([7, 12, 8, 15, 10, 18, $totalFases]),

            Stat::make('Pendientes', $pendientes)
                ->description('Fases sin iniciar')
                ->descriptionIcon('heroicon-o-clock')
                ->color('secondary')
                ->chart([$pendientes, $pendientes - 1, $pendientes + 2, $pendientes]),

            Stat::make('En Progreso', $enProgreso)
                ->description('Fases en las que están trabajando')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('warning')
                ->chart([$enProgreso - 1, $enProgreso + 1, $enProgreso - 2, $enProgreso]),

            Stat::make('Completadas', $completadas)
                ->description('Fases finalizadas')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart([5, 8, 12, 15, 18, 20, $completadas]),
        ];
    }

    protected static ?string $pollingInterval = '30s';
}
