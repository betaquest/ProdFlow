<?php

namespace App\Filament\Resources\ProgramaResource\Pages;

use App\Filament\Resources\ProgramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
                ->color('info'),
            Actions\CreateAction::make(),
        ];
    }
}
