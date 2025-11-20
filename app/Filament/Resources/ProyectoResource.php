<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProyectoResource\Pages;
use App\Models\Proyecto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProyectoResource extends Resource
{
    protected static ?string $model = Proyecto::class;

    protected static ?string $navigationLabel = 'Proyectos';

    protected static ?string $navigationGroup = 'Producción';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('activo', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nombre')
                    ->required()
                    ->disabled(fn ($context) => $context === 'edit' && !auth()->user()->hasRole('Administrador')),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del proyecto')
                    ->required()
                    ->disabled(fn ($context) => $context === 'edit' && !auth()->user()->hasRole('Administrador')),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->disabled(fn ($context) => $context === 'edit' && !auth()->user()->hasRole('Administrador')),
                Forms\Components\Textarea::make('notas')
                    ->label('Notas')
                    ->rows(3),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                Forms\Components\Toggle::make('finalizado')
                    ->label('Finalizado')
                    ->default(false)
                    ->helperText('Marca como finalizado para ocultarlo al crear nuevos programas'),
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
                    ->searchable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('programas_count')
                    ->label('Programas')
                    ->counts('programas')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('finalizado')
                    ->label('Finalizado')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notas')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Activo')
                    ->boolean(),
                Tables\Filters\TernaryFilter::make('finalizado')
                    ->label('Finalizado')
                    ->boolean(),
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
            'index' => Pages\ListProyectos::route('/'),
            'create' => Pages\CreateProyecto::route('/create'),
            'edit' => Pages\EditProyecto::route('/{record}/edit'),
        ];
    }
}
