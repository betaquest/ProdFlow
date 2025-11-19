<?php

namespace App\Filament\Resources\ProgramaResetHistoryResource\Pages;

use App\Filament\Resources\ProgramaResetHistoryResource;
use App\Models\ProgramaResetHistory;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramaResetHistory extends ViewRecord
{
    protected static string $resource = ProgramaResetHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('restaurar')
                ->label('Restaurar Avances')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Restaurar Avances del Programa')
                ->modalDescription(fn () => "¿Estás seguro que deseas restaurar los {$this->record->total_avances_eliminados} avances eliminados del programa '{$this->record->programa_nombre}'?")
                ->modalSubmitActionLabel('Sí, restaurar')
                ->visible(fn () => auth()->user()->can('programas.restaurar_avances'))
                ->action(function () {
                    ProgramaResetHistoryResource::restaurarAvances($this->record);
                }),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->can('programas.eliminar')),
        ];
    }
}
