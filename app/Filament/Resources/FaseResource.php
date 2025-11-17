<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaseResource\Pages;
use App\Models\Fase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaseResource extends Resource
{
    protected static ?string $model = Fase::class;

    protected static ?string $navigationLabel = 'Fases';

    protected static ?string $navigationGroup = 'Producción';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 4;

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
                    ->label('Nombre de la fase')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('alias')
                    ->label('Alias (nombre corto)')
                    ->maxLength(50)
                    ->helperText('Nombre corto para mostrar en el dashboard (opcional)')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('orden')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(fn () => Fase::max('orden') + 1)
                    ->helperText('Orden secuencial de la fase (menor número = primera fase)'),
                Forms\Components\Select::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->nullable()
                    ->helperText('Área responsable de esta fase')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('requiere_aprobacion')
                    ->label('Requiere Aprobación')
                    ->default(true)
                    ->helperText('Si está activo, la fase anterior debe completarse antes de iniciar esta'),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->helperText('Desactiva esta fase para excluirla del flujo de producción'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('orden', 'asc')
            ->defaultPaginationPageOption(50)
            ->reorderable('orden')
            ->columns([
                Tables\Columns\TextColumn::make('orden')
                    ->label('#')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('alias')
                    ->label('Alias')
                    ->searchable()
                    ->placeholder('-')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('area.nombre')
                    ->label('Área')
                    ->badge()
                    ->color('success')
                    ->placeholder('Sin área asignada'),
                Tables\Columns\IconColumn::make('requiere_aprobacion')
                    ->label('Requiere Aprobación')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFases::route('/'),
            'create' => Pages\CreateFase::route('/create'),
            'edit' => Pages\EditFase::route('/{record}/edit'),
        ];
    }
}
