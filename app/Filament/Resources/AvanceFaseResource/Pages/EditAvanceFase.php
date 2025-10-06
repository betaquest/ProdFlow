<?php

namespace App\Filament\Resources\AvanceFaseResource\Pages;

use App\Filament\Resources\AvanceFaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAvanceFase extends EditRecord
{
    protected static string $resource = AvanceFaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
