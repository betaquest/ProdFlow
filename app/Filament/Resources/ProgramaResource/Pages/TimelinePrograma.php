<?php

namespace App\Filament\Resources\ProgramaResource\Pages;

use App\Filament\Resources\ProgramaResource;
use App\Models\Programa;
use App\Models\AvanceFase;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class TimelinePrograma extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ProgramaResource::class;

    protected static string $view = 'filament.resources.programa-resource.pages.timeline-programa';

    protected static ?string $title = 'Timeline del Programa';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getAvances()
    {
        // Obtener las fases configuradas para este programa
        $fasesConfiguradasIds = $this->record->getFasesConfiguradasIds();

        // Obtener todos los avances ordenados por el orden de las fases
        return $this->record->avances()
            ->whereIn('fase_id', $fasesConfiguradasIds)
            ->with(['fase', 'responsable', 'area'])
            ->join('fases', 'avance_fases.fase_id', '=', 'fases.id')
            ->orderBy('fases.orden', 'asc')
            ->select('avance_fases.*')
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('volver')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->url(ProgramaResource::getUrl('index')),
        ];
    }
}
