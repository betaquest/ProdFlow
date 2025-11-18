<?php

namespace App\Filament\Resources\PerfilProgramaResource\Pages;

use App\Filament\Resources\PerfilProgramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerfilProgramas extends ListRecords
{
    protected static string $resource = PerfilProgramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
