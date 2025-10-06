<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\Programa;
use App\Models\AvanceFase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EstadisticasGenerales extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalClientes = Cliente::where('activo', true)->count();
        $totalProyectos = Proyecto::count();
        $totalProgramas = Programa::count();
        $avancesCompletados = AvanceFase::where('estado', 'done')->count();
        $totalAvances = AvanceFase::count();
        $porcentajeCompletado = $totalAvances > 0 ? round(($avancesCompletados / $totalAvances) * 100, 1) : 0;

        return [
            Stat::make('Clientes Activos', $totalClientes)
                ->description('Total de clientes activos')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Proyectos', $totalProyectos)
                ->description('Total de proyectos')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),

            Stat::make('Programas', $totalProgramas)
                ->description('Total de programas')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Progreso Global', $porcentajeCompletado . '%')
                ->description("{$avancesCompletados} de {$totalAvances} avances completados")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }
}
