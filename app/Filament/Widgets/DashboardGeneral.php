<?php

namespace App\Filament\Widgets;

use App\Models\Programa;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DashboardGeneral extends BaseWidget
{
    protected static ?string $heading = '📊 Dashboard General de Producción';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Programa::query()
                    ->with(['proyecto.cliente', 'avances.fase'])
                    ->where('activo', true)
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('proyecto.cliente.nombre')
                    ->label('Cliente')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('proyecto.nombre')
                    ->label('Proyecto')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Programa')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\ViewColumn::make('fases')
                    ->label('Avances por Fase')
                    ->view('filament.tables.columns.fases-status'),
            ])
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50]);
    }
}
