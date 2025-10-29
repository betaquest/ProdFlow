<?php

namespace App\Filament\Resources\ProgramaResource\Pages;

use App\Filament\Resources\ProgramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProgramas extends ListRecords
{
    protected static string $resource = ProgramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reportes')
                ->label('Ver Reportes')
                ->icon('heroicon-o-chart-bar')
                ->url(fn (): string => ProgramaResource::getUrl('reportes'))
                ->color('info')
                ->visible(fn (): bool => ProgramaResource::canViewReports()),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-o-list-bullet')
                ->badge(fn () => \App\Models\Programa::where('activo', true)->count()),

            'en_progreso' => Tab::make('En Progreso')
                ->icon('heroicon-o-arrow-path')
                ->badge(fn () => $this->contarPorEstado('en_progreso'))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $this->filtrarPorEstado($query, 'en_progreso')),

            'pausado' => Tab::make('Pausado')
                ->icon('heroicon-o-pause-circle')
                ->badge(fn () => $this->contarPorEstado('pausado'))
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $this->filtrarPorEstado($query, 'pausado')),

            'completado' => Tab::make('Completado')
                ->icon('heroicon-o-check-circle')
                ->badge(fn () => $this->contarPorEstado('completado'))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $this->filtrarPorEstado($query, 'completado')),

            'sin_iniciar' => Tab::make('Sin Iniciar')
                ->icon('heroicon-o-ellipsis-horizontal-circle')
                ->badge(fn () => $this->contarPorEstado('sin_iniciar'))
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $this->filtrarPorEstado($query, 'sin_iniciar')),
        ];
    }

    protected function contarPorEstado(string $estado): int
    {
        $programas = \App\Models\Programa::with(['avances'])->where('activo', true)->get();
        $count = 0;

        foreach ($programas as $programa) {
            if ($this->determinarEstado($programa) === $estado) {
                $count++;
            }
        }

        return $count;
    }

    protected function filtrarPorEstado(Builder $query, string $estado): Builder
    {
        // Obtener todos los IDs de programas que coinciden con el estado
        $programasIds = \App\Models\Programa::with(['avances', 'avances.fase'])
            ->where('activo', true)
            ->get()
            ->filter(function ($programa) use ($estado) {
                return $this->determinarEstado($programa) === $estado;
            })
            ->pluck('id')
            ->toArray();

        return $query->whereIn('programas.id', $programasIds);
    }

    protected function determinarEstado(\App\Models\Programa $programa): string
    {
        $fasesConfiguradas = $programa->getFasesConfiguradas();

        if ($fasesConfiguradas->isEmpty()) {
            return 'sin_iniciar';
        }

        $totalFases = $fasesConfiguradas->count();
        $fasesCompletadas = 0;
        $hayEnProgreso = false;

        foreach ($fasesConfiguradas as $fase) {
            $avance = $programa->avances->firstWhere('fase_id', $fase->id);
            if ($avance) {
                if ($avance->estado === 'done') {
                    $fasesCompletadas++;
                } elseif ($avance->estado === 'progress') {
                    $hayEnProgreso = true;
                }
            }
        }

        if ($fasesCompletadas === $totalFases) {
            return 'completado';
        } elseif ($hayEnProgreso) {
            return 'en_progreso';
        } elseif ($fasesCompletadas > 0) {
            return 'pausado';
        } else {
            return 'sin_iniciar';
        }
    }
}
