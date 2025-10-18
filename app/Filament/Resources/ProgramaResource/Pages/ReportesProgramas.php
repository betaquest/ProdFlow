<?php

namespace App\Filament\Resources\ProgramaResource\Pages;

use App\Filament\Resources\ProgramaResource;
use App\Models\Programa;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\DB;

class ReportesProgramas extends Page
{
    protected static string $resource = ProgramaResource::class;

    protected static string $view = 'filament.resources.programa-resource.pages.reportes-programas';

    protected static ?string $title = 'Reportes de Programas';

    protected static ?string $navigationLabel = 'Reportes';

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
