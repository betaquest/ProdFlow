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
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del programa')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('responsable_inicial_id')
                    ->label('Responsable')
                    ->options(function () {
                        return User::all()->pluck('name', 'id');
                    })
                    ->default(function () {
                        // Buscar el primer usuario con rol "Ingenieria"
                        $ingenieriaUser = User::role('Ingenieria')->first();
                        return $ingenieriaUser?->id;
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Por defecto se asigna a Ingeniería')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Section::make('⚙️ Configuración de Fases')
                    ->description('Selecciona las fases que aplicarán para este programa. Si no seleccionas ninguna, se usarán todas las fases por defecto.')
                    ->schema([
                        Forms\Components\CheckboxList::make('fases_configuradas')
                            ->label('Fases que aplican')
                            ->options(fn () => Fase::orderBy('orden')->pluck('nombre', 'id'))
                            ->default(fn () => Fase::orderBy('orden')->pluck('id')->toArray())
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->helperText('Marca las fases que aplicarán para este programa. El orden se respeta según la configuración general de fases.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Forms\Components\Textarea::make('notas')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('ultimo_movimiento', 'desc')
            ->defaultPaginationPageOption(50)
            ->poll('30s')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(15)
                    ->tooltip(function ($record): ?string {
                        return $record->descripcion;
                    })
                    ->placeholder('—')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('proyecto.nombre')
                    ->label('Proyecto')
                    ->formatStateUsing(fn ($record) => $record->proyecto->nombre . ' (' . $record->proyecto->cliente->nombre . ')')
                    ->sortable()
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->proyecto->nombre . ' (' . $record->proyecto->cliente->nombre . ')'),
                Tables\Columns\TextColumn::make('fase_actual')
                    ->label('Fase Actual')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // Obtener las fases configuradas para este programa
                        $fasesConfiguradas = $record->getFasesConfiguradas();

                        // Buscar la última fase con estado "progress"
                        foreach ($fasesConfiguradas as $fase) {
                            $avance = $record->avances->firstWhere('fase_id', $fase->id);
                            if ($avance && $avance->estado === 'progress') {
                                return $fase->nombre;
                            }
                        }

                        // Si no hay ninguna en progreso, buscar la última fase completada
                        $ultimaCompletada = null;
                        foreach ($fasesConfiguradas as $fase) {
                            $avance = $record->avances->firstWhere('fase_id', $fase->id);
                            if ($avance && $avance->estado === 'done') {
                                $ultimaCompletada = $fase->nombre;
                            }
                        }

                        if ($ultimaCompletada) {
                            return $ultimaCompletada . ' (Completada)';
                        }

                        // Si no hay ninguna iniciada, mostrar la primera fase configurada
                        return $fasesConfiguradas->first()?->nombre ?? 'Sin iniciar';
                    })
                    ->color(fn ($state) => match (true) {
                        str_contains($state, 'Completada') => 'success',
                        $state === 'Sin iniciar' => 'gray',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('responsable_actual')
                    ->label('Responsable')
                    ->getStateUsing(function ($record) {
                        $fasesConfiguradas = $record->getFasesConfiguradas();

                        // Buscar la fase en progreso
                        foreach ($fasesConfiguradas as $fase) {
                            $avance = $record->avances->firstWhere('fase_id', $fase->id);
                            if ($avance && $avance->estado === 'progress') {
                                // Buscar usuarios con el rol de esta fase
                                $usuarios = User::role($fase->nombre)->get();
                                if ($usuarios->isNotEmpty()) {
                                    return $usuarios->pluck('name')->join(', ');
                                }
                                return '—';
                            }
                        }

                        // Si no hay fase en progreso, buscar la última completada
                        $ultimaFaseCompletada = null;
                        foreach ($fasesConfiguradas as $fase) {
                            $avance = $record->avances->firstWhere('fase_id', $fase->id);
                            if ($avance && $avance->estado === 'done') {
                                $ultimaFaseCompletada = $fase;
                            }
                        }

                        if ($ultimaFaseCompletada) {
                            // Buscar la siguiente fase después de la última completada
                            $siguienteFase = $fasesConfiguradas->where('orden', '>', $ultimaFaseCompletada->orden)->first();
                            if ($siguienteFase) {
                                $usuarios = User::role($siguienteFase->nombre)->get();
                                if ($usuarios->isNotEmpty()) {
                                    return $usuarios->pluck('name')->join(', ') . ' (Pendiente)';
                                }
                            }
                        }

                        // Si no hay nada iniciado, mostrar responsable inicial
                        return $record->responsable_inicial?->name ?? '—';
                    })
                    ->limit(15)
                    ->tooltip(function ($record): ?string {
                        $fasesConfiguradas = $record->getFasesConfiguradas();

                        // Buscar la fase en progreso
                        foreach ($fasesConfiguradas as $fase) {
                            $avance = $record->avances->firstWhere('fase_id', $fase->id);
                            if ($avance && $avance->estado === 'progress') {
                                $usuarios = User::role($fase->nombre)->get();
                                if ($usuarios->isNotEmpty()) {
                                    return $usuarios->pluck('name')->join(', ');
                                }
                                return null;
                            }
                        }

                        // Si no hay fase en progreso, buscar la última completada
                        $ultimaFaseCompletada = null;
                        foreach ($fasesConfiguradas as $fase) {
                            $avance = $record->avances->firstWhere('fase_id', $fase->id);
                            if ($avance && $avance->estado === 'done') {
                                $ultimaFaseCompletada = $fase;
                            }
                        }

                        if ($ultimaFaseCompletada) {
                            $siguienteFase = $fasesConfiguradas->where('orden', '>', $ultimaFaseCompletada->orden)->first();
                            if ($siguienteFase) {
                                $usuarios = User::role($siguienteFase->nombre)->get();
                                if ($usuarios->isNotEmpty()) {
                                    return $usuarios->pluck('name')->join(', ') . ' (Pendiente)';
                                }
                            }
                        }

                        return $record->responsable_inicial?->name ?? null;
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('responsable_inicial', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estado_proceso')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $fasesConfiguradas = $record->getFasesConfiguradas();

                        if ($fasesConfiguradas->isEmpty()) {
                            return 'Sin configurar';
                        }

                        $totalFases = $fasesConfiguradas->count();
                        $fasesCompletadas = 0;
                        $hayEnProgreso = false;

                        foreach ($fasesConfiguradas as $fase) {
                            $avance = $record->avances->firstWhere('fase_id', $fase->id);
                            if ($avance) {
                                if ($avance->estado === 'done') {
                                    $fasesCompletadas++;
                                } elseif ($avance->estado === 'progress') {
                                    $hayEnProgreso = true;
                                }
                            }
                        }

                        if ($fasesCompletadas === $totalFases) {
                            return '✅ Completado';
                        } elseif ($hayEnProgreso) {
                            return "⏳ En Progreso ($fasesCompletadas/$totalFases)";
                        } elseif ($fasesCompletadas > 0) {
                            return "⏸️ Pausado ($fasesCompletadas/$totalFases)";
                        } else {
                            return '⬜ Sin Iniciar';
                        }
                    })
                    ->color(fn ($state) => match (true) {
                        str_contains($state, '✅') => 'success',
                        str_contains($state, '⏳') => 'warning',
                        str_contains($state, '⏸️') => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ultimo_movimiento')
                    ->label('Último Movimiento')
                    ->getStateUsing(function ($record) {
                        // Buscar la fecha más reciente entre todos los avances
                        $ultimaFecha = $record->avances
                            ->max('updated_at');

                        if (!$ultimaFecha) {
                            return $record->created_at;
                        }

                        return $ultimaFecha;
                    })
                    ->dateTime('d/m/Y H:i')
                    ->sortable(query: function ($query, $direction) {
                        return $query
                            ->leftJoin('avance_fases', 'programas.id', '=', 'avance_fases.programa_id')
                            ->select('programas.*')
                            ->selectRaw('COALESCE(MAX(avance_fases.updated_at), programas.created_at) as ultimo_movimiento')
                            ->groupBy('programas.id', 'programas.proyecto_id', 'programas.nombre', 'programas.descripcion', 'programas.fases_configuradas', 'programas.responsable_inicial_id', 'programas.notas', 'programas.activo', 'programas.created_at', 'programas.updated_at')
                            ->orderBy('ultimo_movimiento', $direction);
                    })
                    ->description(fn ($record) => 'Hace ' . $record->avances->max('updated_at')?->diffForHumans() ?? $record->created_at->diffForHumans())
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('activo')->label('Activo')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(20)
                    ->tooltip(function ($record): ?string {
                        return $record->notas;
                    })
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Activo/Inactivo')
                    ->boolean(),
                Tables\Filters\SelectFilter::make('estado_proceso')
                    ->label('Estado del Proceso')
                    ->options([
                        'en_progreso' => '⏳ En Progreso',
                        'pausado' => '⏸️ Pausado',
                        'completado' => '✅ Completado',
                        'sin_iniciar' => '⬜ Sin Iniciar',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        $estado = $data['value'];

                        // Obtener IDs de programas que coinciden con el estado
                        $programasIds = \App\Models\Programa::with(['avances'])
                            ->get()
                            ->filter(function ($programa) use ($estado) {
                                return static::determinarEstadoPrograma($programa) === $estado;
                            })
                            ->pluck('id')
                            ->toArray();

                        return $query->whereIn('id', $programasIds);
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

    protected static function determinarEstadoPrograma(\App\Models\Programa $programa): string
    {
        $fasesConfiguradas = $programa->getFasesConfiguradas();

        if ($fasesConfiguradas->isEmpty()) {
            return 'sin_iniciar';
        }

        $totalFases = $fasesConfiguradas->count();
        $fasesCompletadas = 0;
        $hayEnProgreso = false;

        foreach ($fasesConfiguradas as $fase) {
            $avance = $programa->avances->firstWhere('fase_id', $fase->id);
            if ($avance) {
                if ($avance->estado === 'done') {
                    $fasesCompletadas++;
                } elseif ($avance->estado === 'progress') {
                    $hayEnProgreso = true;
                }
            }
        }

        if ($fasesCompletadas === $totalFases) {
            return 'completado';
        } elseif ($hayEnProgreso) {
            return 'en_progreso';
        } elseif ($fasesCompletadas > 0) {
            return 'pausado';
        } else {
            return 'sin_iniciar';
        }
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
        ];
    }
}
