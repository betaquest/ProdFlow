<?php

namespace App\Filament\Resources\ProgramaResetHistoryResource\Pages;

use App\Filament\Resources\ProgramaResetHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgramaResetHistory extends EditRecord
{
    protected static string $resource = ProgramaResetHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
