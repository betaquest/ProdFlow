<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DashboardResource\Pages;
use App\Models\Dashboard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DashboardResource extends Resource
{
    protected static ?string $model = Dashboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-tv';

    protected static ?string $navigationLabel = 'Dashboards';

    protected static ?string $navigationGroup = 'Configuración';

    // protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 2;

    // Restringir acceso solo a Administradores
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('Administrador') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del Dashboard')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->label('Identificador (URL)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->prefix('/dashboards/'),
                Forms\Components\Textarea::make('descripcion')->rows(3),
                Forms\Components\Toggle::make('activo')->default(true),
                Forms\Components\Textarea::make('criterios')
                    ->label('Criterios (JSON opcional)')
                    ->helperText('Puedes definir filtros manuales, por ejemplo {"cliente_id": 2}')
                    ->rows(3),
                Forms\Components\TextInput::make('tiempo_actualizacion')
                    ->numeric()
                    ->label('Tiempo de actualización (segundos)')
                    ->default(30)
                    ->minValue(5)
                    ->helperText('Define cada cuántos segundos se actualiza la pantalla automáticamente.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('nombre', 'asc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('URL')->formatStateUsing(fn ($state) => "/dashboards/{$state}"),
                Tables\Columns\IconColumn::make('activo')->boolean()->label('Activo'),
                Tables\Columns\TextColumn::make('descripcion')->limit(30),
                Tables\Columns\TextColumn::make('tiempo_actualizacion')
                    ->label('Tiempo (segundos)')
                    ->sortable()
                    ->alignCenter()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state.' seg'),
            ])->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('ver')
                        ->label('Ver Dashboard')
                        ->icon('heroicon-o-eye')
                        ->url(fn ($record) => url("/dashboards/{$record->slug}"))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('copiar')
                        ->label('Copiar link')
                        ->icon('heroicon-o-link')
                        ->action(function ($record, $livewire) {
                            $url = url("/dashboards/{$record->slug}");
                            $livewire->js("navigator.clipboard.writeText('{$url}'); alert('Enlace copiado: {$url}');");
                        }),

                ]),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDashboards::route('/'),
            'create' => Pages\CreateDashboard::route('/create'),
            'edit' => Pages\EditDashboard::route('/{record}/edit'),
        ];
    }
}
