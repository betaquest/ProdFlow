<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramaResource\Pages;
use App\Models\Programa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProgramaResource extends Resource
{
    protected static ?string $model = Programa::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Programas';

    protected static ?string $navigationGroup = 'Producción';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('activo', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('proyecto_id')
                    ->label('Proyecto')
                    ->relationship('proyecto', 'nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre . ' (' . $record->cliente->nombre . ')')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del programa')
                    ->required(),
                Forms\Components\Textarea::make('descripcion')->label('Descripción')->rows(3),
                Forms\Components\Textarea::make('notas')->label('Notas')->rows(3),
                Forms\Components\Toggle::make('activo')->label('Activo')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('nombre', 'asc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('proyecto.nombre')
                    ->label('Proyecto')
                    ->formatStateUsing(fn ($record) => $record->proyecto->nombre . ' (' . $record->proyecto->cliente->nombre . ')')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('activo')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('descripcion')->limit(30),
                Tables\Columns\TextColumn::make('notas')->limit(30),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')
                    ->boolean(),
            ]);
    }

    public static function canViewReports(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        $policy = app(\App\Policies\ProgramaPolicy::class);

        return $policy->viewReports($user);
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
            'index' => Pages\ListProgramas::route('/'),
            'create' => Pages\CreatePrograma::route('/create'),
            'edit' => Pages\EditPrograma::route('/{record}/edit'),
            'reportes' => Pages\ReportesProgramas::route('/reportes'),
        ];
    }
}
