<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerfilProgramaResource\Pages;
use App\Models\PerfilPrograma;
use App\Models\Fase;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PerfilProgramaResource extends Resource
{
    protected static ?string $model = PerfilPrograma::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Perfiles de Programa';

    protected static ?string $navigationGroup = 'Configuración';

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
                    ->label('Nombre del Perfil')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Ej: In-House, Walk-In, Express')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->placeholder('Describe el propósito de este perfil...')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->helperText('Desactiva este perfil para que no esté disponible al crear programas'),

                Forms\Components\Toggle::make('predeterminado')
                    ->label('Perfil Predeterminado')
                    ->default(false)
                    ->helperText('Solo un perfil puede ser predeterminado. Se seleccionará automáticamente al crear programas.')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            // Si se marca como predeterminado, advertir que otros se desmarcarán
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Perfil predeterminado')
                                ->body('Al guardar, este será el único perfil predeterminado.')
                                ->send();
                        }
                    }),

                Forms\Components\Section::make('⚙️ Configuración de Fases')
                    ->description('Define las fases, áreas responsables y el orden para este perfil.')
                    ->schema([
                        Forms\Components\Repeater::make('configuracion.fases')
                            ->label('Fases del Perfil')
                            ->schema([
                                Forms\Components\Select::make('fase_id')
                                    ->label('Fase')
                                    ->options(fn () => Fase::where('activo', true)->orderBy('orden')->pluck('nombre', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->columnSpan(2),

                                Forms\Components\Select::make('area_id')
                                    ->label('Área Responsable')
                                    ->options(fn () => Area::orderBy('nombre')->pluck('nombre', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('orden')
                                    ->label('Orden')
                                    ->required()
                                    ->numeric()
                                    ->default(fn ($get) => count($get('../../configuracion.fases') ?? []) + 1)
                                    ->helperText('Orden de ejecución (1 = primera fase)')
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('requiere_aprobacion')
                                    ->label('Requiere Aprobación')
                                    ->default(true)
                                    ->helperText('La fase anterior debe completarse')
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
                            ->defaultItems(0)
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                isset($state['fase_id'])
                                    ? Fase::find($state['fase_id'])?->nombre . ' (Orden: ' . ($state['orden'] ?? '?') . ')'
                                    : 'Nueva fase'
                            )
                            ->addActionLabel('Agregar Fase')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->descripcion)
                    ->placeholder('Sin descripción'),

                Tables\Columns\TextColumn::make('fases_count')
                    ->label('# Fases')
                    ->getStateUsing(fn ($record) => count($record->configuracion['fases'] ?? []))
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('predeterminado')
                    ->label('Predeterminado')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('programas_count')
                    ->label('Programas')
                    ->counts('programas')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Activo')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),

                Tables\Filters\TernaryFilter::make('predeterminado')
                    ->label('Predeterminado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo predeterminado')
                    ->falseLabel('No predeterminados'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->programas()->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('No se puede eliminar')
                                ->body('Este perfil tiene programas asociados.')
                                ->send();

                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('predeterminado', 'desc');
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
            'index' => Pages\ListPerfilProgramas::route('/'),
            'create' => Pages\CreatePerfilPrograma::route('/create'),
            'edit' => Pages\EditPerfilPrograma::route('/{record}/edit'),
        ];
    }
}
