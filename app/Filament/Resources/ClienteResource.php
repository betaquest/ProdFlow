<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $navigationGroup = 'Producción';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('alias')
                    ->label('Alias / Apodo')
                    ->maxLength(255),

                Forms\Components\TextInput::make('contacto'),
                Forms\Components\TextInput::make('telefono'),
                Forms\Components\TextInput::make('rfc'),
                Forms\Components\Textarea::make('notas')
                    ->rows(3)
                    ->label('Notas / Comentarios')->columnSpanFull(),
                Forms\Components\Toggle::make('activo')
                    ->label('Cliente activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('alias')
                    ->label('Alias')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('proyectos_count')
                    ->label('Proyectos')
                    ->counts('proyectos')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->label('Activo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('contacto')
                    ->label('Contacto')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('rfc')
                    ->label('RFC')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notas')
                    ->limit(30)
                    ->label('Notas')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultPaginationPageOption(50)
            ->paginationPageOptions([25, 50, 100, 250])
            ->defaultSort('nombre', 'asc')
            ->striped()
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado del cliente')
                    ->boolean(),
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
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\ClienteExporter::class),
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
