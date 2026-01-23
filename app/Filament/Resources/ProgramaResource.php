<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramaResource\Pages;
use App\Models\Programa;
use App\Models\User;
use App\Models\Fase;
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
        // Contar programas que NO están completados
        $programas = static::getModel()::with(['avances'])->where('activo', true)->get();
        $noCompletados = 0;

        foreach ($programas as $programa) {
            $fasesConfiguradas = $programa->getFasesConfiguradas();

            if ($fasesConfiguradas->isEmpty()) {
                $noCompletados++;
                continue;
            }

            $totalFases = $fasesConfiguradas->count();
            $fasesCompletadas = 0;

            foreach ($fasesConfiguradas as $fase) {
                $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                if ($avance && $avance->estado === 'done') {
                    $fasesCompletadas++;
                }
            }

            // Si no todas las fases están completadas, contar como no completado
            if ($fasesCompletadas < $totalFases) {
                $noCompletados++;
            }
        }

        return (string) $noCompletados;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // Método helper para obtener perfiles disponibles una sola vez (caché)
    protected static function getPerfilesDisponibles()
    {
        static $cache = null;
        
        if ($cache !== null) {
            return $cache;
        }

        $user = auth()->user();
        $query = \App\Models\PerfilPrograma::where('activo', true);

        // Si es administrador
        if ($user->hasRole('Administrador')) {
            $cache = $query->get();
        } else {
            // Para otros usuarios, filtrar por área
            $userAreaId = $user->area_id;
            $cache = $query->where(function ($q) use ($userAreaId) {
                $q->whereDoesntHave('areas')
                  ->orWhereHas('areas', function ($areaQuery) use ($userAreaId) {
                      $areaQuery->where('areas.id', $userAreaId);
                  });
            })->get();
        }

        return $cache;
    }

    // Método helper para obtener fases activas una sola vez (caché)
    protected static function getFasesActivas()
    {
        static $cache = null;
        
        if ($cache === null) {
            $cache = Fase::where('activo', true)->orderBy('orden')->pluck('nombre', 'id');
        }
        
        return $cache;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('proyecto_id')
                    ->label('Proyecto')
                    ->relationship('proyecto', 'nombre', fn ($query) => $query->where('activo', true)->where('finalizado', false)->with('cliente'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre . ' (' . $record->cliente->nombre . ')')
                    ->searchable(['nombre'])
                    ->preload()
                    ->required()
                    ->columnSpanFull()
                    ->disabled(fn ($context) => $context === 'edit' && !auth()->user()->can('programas.editar')),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del programa')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->disabled(fn ($context) => $context === 'edit' && !auth()->user()->can('programas.editar')),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(fn ($context) => $context === 'edit' && !auth()->user()->can('programas.editar')),

                Forms\Components\Hidden::make('perfil_programa_id')
                    ->default(function () {
                        $perfilesDisponibles = static::getPerfilesDisponibles();

                        // Si solo hay un perfil disponible, seleccionarlo automáticamente
                        if ($perfilesDisponibles->count() === 1) {
                            return $perfilesDisponibles->first()->id;
                        }

                        // Si hay más de uno, intentar obtener el predeterminado
                        $predeterminado = $perfilesDisponibles->where('predeterminado', true)->first();
                        return $predeterminado?->id;
                    })
                    ->dehydrated()
                    ->visible(fn () => static::getPerfilesDisponibles()->count() === 1),

                Forms\Components\Section::make('🎯 Perfil de Programa')
                    ->description('Selecciona un perfil predefinido que determinará las fases y áreas del programa.')
                    ->schema([
                        Forms\Components\Select::make('perfil_programa_id')
                            ->label('Perfil')
                            ->options(fn () => static::getPerfilesDisponibles()->pluck('nombre', 'id'))
                            ->default(function () {
                                $perfilesDisponibles = static::getPerfilesDisponibles();

                                // Si solo hay un perfil disponible, seleccionarlo automáticamente
                                if ($perfilesDisponibles->count() === 1) {
                                    return $perfilesDisponibles->first()->id;
                                }

                                // Si hay más de uno, intentar obtener el predeterminado
                                $predeterminado = $perfilesDisponibles->where('predeterminado', true)->first();
                                return $predeterminado?->id;
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('El perfil define las fases, áreas responsables y el orden del flujo de trabajo.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Si selecciona un perfil, limpiar fases_configuradas
                                if ($state) {
                                    $set('fases_configuradas', null);
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->hidden(fn () => static::getPerfilesDisponibles()->count() === 1),

                Forms\Components\Section::make('⚙️ Configuración Manual de Fases')
                    ->description('Alternativamente, puedes configurar las fases manualmente (ignora el perfil seleccionado).')
                    ->schema([
                        Forms\Components\CheckboxList::make('fases_configuradas')
                            ->label('Fases que aplican')
                            ->options(fn () => static::getFasesActivas())
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->helperText('⚠️ Si seleccionas fases manualmente, se ignorará el perfil seleccionado arriba.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(true)
                    ->hidden(fn () => !auth()->user()->can('programas.configurar_fases_manual')),

                Forms\Components\Textarea::make('notas')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->columnSpanFull()
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('updated_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn ($query) => $query->withOptimizations())
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(20)
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('proyecto.nombre')
                    ->label('Proyecto')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->description(fn ($record) => $record->proyecto->cliente->nombre ?? '')
                    ->wrap(),
                Tables\Columns\TextColumn::make('perfilPrograma.nombre')
                    ->label('Perfil')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin perfil')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fase_actual')
                    ->label('Fase Actual')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        str_contains($state, '✓') => 'success',
                        $state === 'Sin iniciar' => 'gray',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('responsableInicial.name')
                    ->label('Responsable')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estado_proceso')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        str_contains($state, '✅') => 'success',
                        str_contains($state, '⏳') => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Último Movimiento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('activo')->label('Activo')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(20)
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Activo/Inactivo')
                    ->query(fn ($query, $state) =>
                        match ($state) {
                            '1' => $query->where('programas.activo', true),
                            '0' => $query->where('programas.activo', false),
                            default => $query,
                        }
                    )
                    ->default('1'),
                Tables\Filters\SelectFilter::make('estado_proceso')
                    ->label('Estado del Proceso')
                    ->options([
                        'en_progreso' => '⏳ En Progreso',
                        'avanzando' => '✅ Avanzando',
                        'sin_iniciar' => '⬜ Sin Iniciar',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        $estado = $data['value'];

                        return match($estado) {
                            'en_progreso' => $query->whereHas('avances', fn($q) => $q->where('estado', 'progress')),
                            'avanzando' => $query->whereHas('avances', fn($q) => $q->where('estado', 'done')),
                            'sin_iniciar' => $query->whereDoesntHave('avances'),
                            default => $query
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('timeline')
                    ->label('')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->tooltip('Ver Timeline')
                    ->url(fn (Programa $record): string => static::getUrl('timeline', ['record' => $record])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'timeline' => Pages\TimelinePrograma::route('/{record}/timeline'),
            'reportes' => Pages\ReportesProgramas::route('/reportes'),
            'reporte-detalle' => Pages\ReporteDetalleProgramas::route('/reporte-detalle'),
        ];
    }
}
