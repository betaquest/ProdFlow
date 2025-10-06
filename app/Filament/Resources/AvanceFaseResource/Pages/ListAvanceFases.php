<?php

namespace App\Filament\Resources\AvanceFaseResource\Pages;

use App\Filament\Resources\AvanceFaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAvanceFases extends ListRecords
{
    protected static string $resource = AvanceFaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
