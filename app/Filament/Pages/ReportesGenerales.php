<?php

namespace App\Filament\Pages;

use App\Models\Programa;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ReportesGenerales extends Page
{
    protected static string $view = 'filament.pages.reportes-generales';

    protected static ?string $title = 'Reportes Generales';

    protected static ?string $navigationLabel = 'Reportes Generales';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->can('programas.ver_reportes');
    }

    public function getViewData(): array
    {
        return [
            'totalProgramas' => Programa::count(),
            'programasActivos' => Programa::where('activo', true)->count(),
            'programasInactivos' => Programa::where('activo', false)->count(),
            'programasPorProyecto' => $this->getProgramasPorProyecto(),
            'programasPorCliente' => $this->getProgramasPorCliente(),
        ];
    }

    protected function getProgramasPorProyecto(): array
    {
        return Programa::select('proyectos.nombre as proyecto_nombre', DB::raw('count(*) as total'))
            ->join('proyectos', 'programas.proyecto_id', '=', 'proyectos.id')
            ->groupBy('proyectos.id', 'proyectos.nombre')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    protected function getProgramasPorCliente(): array
    {
        return Programa::select('clientes.nombre as cliente_nombre', DB::raw('count(*) as total'))
            ->join('proyectos', 'programas.proyecto_id', '=', 'proyectos.id')
            ->join('clientes', 'proyectos.cliente_id', '=', 'clientes.id')
            ->groupBy('clientes.id', 'clientes.nombre')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }
}
