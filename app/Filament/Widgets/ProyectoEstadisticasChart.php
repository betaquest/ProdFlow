<?php

namespace App\Filament\Widgets;

use App\Models\Programa;
use App\Models\AvanceFase;
use Filament\Widgets\ChartWidget;

class ProyectoEstadisticasChart extends ChartWidget
{
    protected static ?string $heading = 'Estado de Programas';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $pending = AvanceFase::where('estado', 'pending')->count();
        $progress = AvanceFase::where('estado', 'progress')->count();
        $done = AvanceFase::where('estado', 'done')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Estado de Avances',
                    'data' => [$pending, $progress, $done],
                    'backgroundColor' => [
                        'rgb(255, 205, 86)',
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)',
                    ],
                ],
            ],
            'labels' => ['Pendiente', 'En Progreso', 'Completado'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
