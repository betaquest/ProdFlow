<?php

namespace App\Filament\Pages;

use App\Models\AvanceFase;
use App\Models\Fase;
use App\Models\User;
use App\Models\Programa;
use App\Models\Proyecto;
use App\Notifications\FaseLiberada;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Components\Tab;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class MisFases extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $activeTab = 'todos';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.mis-fases';

    protected static ?string $navigationLabel = 'Proceso';

    protected static ?string $title = 'Mi Proceso de Trabajo';

    protected static ?int $navigationSort = 1;

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public static function getNavigationBadge(): ?string
    {
        $query = AvanceFase::whereIn('estado', ['pending', 'progress'])
            ->whereHas('programa', function ($q) {
                $q->where('activo', true);
            });

        // Solo el Administrador ve el total de todas las tareas
        // Los demás usuarios solo ven las de su área
        if (!Auth::user()->hasRole('Administrador')) {
            $query->where('area_id', Auth::user()->area_id);
        }

        $count = $query->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $query = AvanceFase::whereIn('estado', ['pending', 'progress'])
            ->whereHas('programa', function ($q) {
                $q->where('activo', true);
            });

        // Solo el Administrador ve el total de todas las tareas
        // Los demás usuarios solo ven las de su área
        if (!Auth::user()->hasRole('Administrador')) {
            $query->where('area_id', Auth::user()->area_id);
        }

        $count = $query->count();

        if ($count === 0) {
            return null;
        }

        // Si hay tareas en progreso, mostrar en warning (amarillo)
        $queryInProgress = AvanceFase::where('estado', 'progress');

        if (!Auth::user()->hasRole('Administrador')) {
            $queryInProgress->where('area_id', Auth::user()->area_id);
        }

        $inProgress = $queryInProgress->exists();

        return $inProgress ? 'warning' : 'danger';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crearPrograma')
                ->label('Crear Programa')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->visible(function () {
                    $user = Auth::user();
                    // Verificar si el usuario tiene permiso para crear programas
                    return $user->can('programas.crear') ||
                           $user->hasRole('Administrador') ||
                           ($user->area && $user->area->hasPermission('programas.crear'));
                })
                ->slideOver()
                ->form([
                    Forms\Components\Toggle::make('mostrar_finalizados')
                        ->label('Mostrar Proyectos Finalizados')
                        ->default(false)
                        ->live()
                        ->helperText('Activar para ver proyectos marcados como finalizados')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('proyecto_id')
                        ->label('Proyecto')
                        ->options(function ($get) {
                            $query = Proyecto::with('cliente');

                            // Si no se marca mostrar_finalizados, filtrar solo no finalizados
                            if (!$get('mostrar_finalizados')) {
                                $query->where('finalizado', false);
                            }

                            return $query->get()->mapWithKeys(function ($proyecto) {
                                $finalizadoTag = $proyecto->finalizado ? ' [FINALIZADO]' : '';
                                return [$proyecto->id => $proyecto->nombre . ' (' . $proyecto->cliente->nombre . ')' . $finalizadoTag];
                            });
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre del programa')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('perfil_programa_id')
                        ->default(function () {
                            $user = auth()->user();
                            $query = \App\Models\PerfilPrograma::where('activo', true);

                            // Si es administrador
                            if ($user->hasRole('Administrador')) {
                                $perfilesDisponibles = $query->get();
                            } else {
                                // Para otros usuarios, filtrar por área
                                $userAreaId = $user->area_id;
                                $perfilesDisponibles = $query->where(function ($q) use ($userAreaId) {
                                    $q->whereDoesntHave('areas')
                                      ->orWhereHas('areas', function ($areaQuery) use ($userAreaId) {
                                          $areaQuery->where('areas.id', $userAreaId);
                                      });
                                })->get();
                            }

                            // Si solo hay un perfil disponible, seleccionarlo automáticamente
                            if ($perfilesDisponibles->count() === 1) {
                                return $perfilesDisponibles->first()->id;
                            }

                            // Si hay más de uno, intentar obtener el predeterminado de los disponibles
                            $predeterminado = $perfilesDisponibles->where('predeterminado', true)->first();
                            return $predeterminado?->id;
                        })
                        ->dehydrated()
                        ->visible(function () {
                            $user = auth()->user();
                            $query = \App\Models\PerfilPrograma::where('activo', true);

                            // Si es administrador
                            if ($user->hasRole('Administrador')) {
                                $perfilesDisponibles = $query->get();
                            } else {
                                // Para otros usuarios, filtrar por área
                                $userAreaId = $user->area_id;
                                $perfilesDisponibles = $query->where(function ($q) use ($userAreaId) {
                                    $q->whereDoesntHave('areas')
                                      ->orWhereHas('areas', function ($areaQuery) use ($userAreaId) {
                                          $areaQuery->where('areas.id', $userAreaId);
                                      });
                                })->get();
                            }

                            // Mostrar el hidden solo si hay 1 perfil (cuando la sección está oculta)
                            return $perfilesDisponibles->count() === 1;
                        }),

                    Forms\Components\Section::make('🎯 Perfil de Programa')
                        ->description('Selecciona un perfil predefinido que determinará las fases y áreas del programa.')
                            ->schema([
                                Forms\Components\Select::make('perfil_programa_id')
                                    ->label('⚠️ PERFIL DEL PROGRAMA')
                                    ->options(function () {
                                        $user = auth()->user();
                                        $query = \App\Models\PerfilPrograma::where('activo', true);

                                        // Si es administrador, mostrar todos los perfiles
                                        if ($user->hasRole('Administrador')) {
                                            return $query->pluck('nombre', 'id');
                                        }

                                        // Si no es administrador, filtrar por área del usuario
                                        $userAreaId = $user->area_id;

                                        // Obtener perfiles que no tienen áreas asignadas (disponibles para todos)
                                        // O que tienen el área del usuario
                                        return $query->where(function ($q) use ($userAreaId) {
                                            $q->whereDoesntHave('areas')  // Perfiles sin áreas asignadas
                                              ->orWhereHas('areas', function ($areaQuery) use ($userAreaId) {
                                                  $areaQuery->where('areas.id', $userAreaId);
                                              });
                                        })->pluck('nombre', 'id');
                                    })
                                    ->default(function () {
                                        $user = auth()->user();
                                        $query = \App\Models\PerfilPrograma::where('activo', true);

                                        // Si es administrador
                                        if ($user->hasRole('Administrador')) {
                                            $perfilesDisponibles = $query->get();
                                        } else {
                                            // Para otros usuarios, filtrar por área
                                            $userAreaId = $user->area_id;
                                            $perfilesDisponibles = $query->where(function ($q) use ($userAreaId) {
                                                $q->whereDoesntHave('areas')
                                                  ->orWhereHas('areas', function ($areaQuery) use ($userAreaId) {
                                                      $areaQuery->where('areas.id', $userAreaId);
                                                  });
                                            })->get();
                                        }

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
                                    ->helperText('⚠️ IMPORTANTE: Walk-In = 3 fases (sin Ingeniería) | In-House = 9 fases (completo)')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('fases_configuradas', null);
                                        }
                                    })
                                    ->columnSpanFull(),
                            ])
                        ->collapsible()
                        ->collapsed(false)
                        ->hidden(function () {
                            $user = auth()->user();
                            $query = \App\Models\PerfilPrograma::where('activo', true);

                            // Si es administrador
                            if ($user->hasRole('Administrador')) {
                                $perfilesDisponibles = $query->get();
                            } else {
                                // Para otros usuarios, filtrar por área
                                $userAreaId = $user->area_id;
                                $perfilesDisponibles = $query->where(function ($q) use ($userAreaId) {
                                    $q->whereDoesntHave('areas')
                                      ->orWhereHas('areas', function ($areaQuery) use ($userAreaId) {
                                          $areaQuery->where('areas.id', $userAreaId);
                                      });
                                })->get();
                            }

                            // Ocultar la sección si solo hay 1 perfil disponible
                            return $perfilesDisponibles->count() === 1;
                        }),
                    Forms\Components\Section::make('⚙️ Configuración Manual de Fases')
                        ->description('Alternativamente, puedes configurar las fases manualmente (ignora el perfil seleccionado).')
                        ->schema([
                            Forms\Components\CheckboxList::make('fases_configuradas')
                                ->label('Fases que aplican')
                                ->options(fn () => Fase::where('activo', true)->orderBy('orden')->pluck('nombre', 'id'))
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
                        ->columnSpanFull(),
                ])
                    ->action(function (array $data): void {
                        $perfilId = $data['perfil_programa_id'] ?? \App\Models\PerfilPrograma::predeterminado()->first()?->id;

                        $programa = Programa::create([
                            'proyecto_id' => $data['proyecto_id'],
                            'perfil_programa_id' => $perfilId,
                            'nombre' => $data['nombre'],
                            'descripcion' => $data['descripcion'] ?? null,
                            'responsable_inicial_id' => null,
                            'notas' => $data['notas'] ?? null,
                            'activo' => $data['activo'] ?? true,
                            'fases_configuradas' => $data['fases_configuradas'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Programa creado exitosamente')
                            ->success()
                            ->send();
                    }),
        ];
    }

    public function getTabs(): array
    {
        $baseQuery = AvanceFase::query()
            ->whereHas('programa', function ($q) {
                $q->where('activo', true);
            });

        // Filtrar por área si no es administrador
        if (!Auth::user()->hasRole('Administrador')) {
            $baseQuery->where('area_id', Auth::user()->area_id);
        }

        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-o-list-bullet')
                ->badge(fn () => (clone $baseQuery)->count()),

            'pending' => Tab::make('Pendiente')
                ->icon('heroicon-o-clock')
                ->badge(fn () => (clone $baseQuery)->where('estado', 'pending')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'pending')),

            'progress' => Tab::make('En Progreso')
                ->icon('heroicon-o-arrow-path')
                ->badge(fn () => (clone $baseQuery)->where('estado', 'progress')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'progress')),

            'done' => Tab::make('Finalizado')
                ->icon('heroicon-o-check-circle')
                ->badge(fn () => (clone $baseQuery)->where('estado', 'done')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'done')),
        ];
    }

    public function table(Table $table): Table
    {
        $query = AvanceFase::query()
            ->with(['programa.proyecto.cliente', 'fase', 'area'])
            ->whereHas('programa', function ($q) {
                $q->where('activo', true);
            });

        // Solo el Administrador puede ver todos los procesos
        // Los demás usuarios solo ven las de su área
        if (!Auth::user()->hasRole('Administrador')) {
            $query->where('area_id', Auth::user()->area_id);
        }

        // Aplicar filtro del tab activo
        if ($this->activeTab !== 'todos') {
            $query->where('estado', $this->activeTab);
        }

        // Ordenamiento: nuevos primero (por fecha de creación)
        $query->orderBy('created_at', 'desc');

        return $table
            ->query($query)
            ->striped()
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('programa.proyecto.cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('programa.proyecto.nombre')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('programa.nombre')
                    ->label('Programa')
                    ->searchable()
                    ->sortable()
                    ->description(fn (AvanceFase $record): string => $record->programa->descripcion ?? ''),

                Tables\Columns\TextColumn::make('programa.perfilPrograma.nombre')
                    ->label('Perfil')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fase.nombre')
                    ->label('Fase')
                    ->formatStateUsing(fn (AvanceFase $record): string =>
                        $record->fase->alias ?: $record->fase->nombre
                    )
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('area.nombre')
                    ->label('Área')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => Auth::user()->hasRole('Administrador')),

                Tables\Columns\TextColumn::make('responsable.name')
                    ->label('Responsable')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin asignar')
                    ->visible(fn () => Auth::user()->hasRole('Administrador')),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (AvanceFase $record): string => match ($record->estado) {
                        'pending' => $record->fecha_liberacion ? 'Liberada' : 'Pendiente',
                        'progress' => 'En Progreso',
                        'done' => 'Finalizado',
                        default => $record->estado,
                    })
                    ->color(fn (AvanceFase $record): string => match (true) {
                        $record->estado === 'pending' && !$record->fecha_liberacion => 'gray',
                        $record->estado === 'pending' && $record->fecha_liberacion => 'info',
                        $record->estado === 'progress' => 'warning',
                        $record->estado === 'done' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (AvanceFase $record): string => match ($record->estado) {
                        'pending' => $record->fecha_liberacion ? 'heroicon-o-bell-alert' : 'heroicon-o-clock',
                        'progress' => 'heroicon-o-arrow-path',
                        'done' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->extraAttributes(fn (AvanceFase $record): array =>
                        $record->fecha_liberacion && $record->estado === 'pending'
                            ? ['title' => '📅 Liberada: ' . $record->fecha_liberacion->format('d/m/Y H:i')]
                            : []
                    )
                    ->description(fn (AvanceFase $record): ?string =>
                        $record->fecha_liberacion && $record->estado === 'pending'
                            ? 'Notificada: ' . $record->fecha_liberacion->format('d/m/Y H:i')
                            : null
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_liberacion')
                    ->label('Liberación')
                    ->html()
                    ->formatStateUsing(function (AvanceFase $record): ?string {
                        if (!$record->fecha_liberacion) {
                            return null;
                        }

                        $fecha = $record->fecha_liberacion->format('d/m/Y H:i');

                        // Buscar si hay notas de la fase anterior
                        $fasesConfiguradasIds = $record->programa->getFasesConfiguradasIds();
                        $fasesConfiguradas = Fase::whereIn('id', $fasesConfiguradasIds)
                            ->orderBy('orden', 'asc')
                            ->get();

                        $faseAnterior = $fasesConfiguradas->where('orden', '<', $record->fase->orden)
                            ->sortByDesc('orden')
                            ->first();

                        $tieneNotas = false;
                        if ($faseAnterior) {
                            $avanceAnterior = AvanceFase::where('programa_id', $record->programa_id)
                                ->where('fase_id', $faseAnterior->id)
                                ->first();

                            if ($avanceAnterior && $avanceAnterior->notas_finalizacion) {
                                $tieneNotas = true;
                            }
                        }

                        return $tieneNotas ? $fecha . ' <span style="color: #9ca3af; opacity: 0.7;">📝</span>' : $fecha;
                    })
                    ->sortable()
                    ->placeholder('—')
                    ->tooltip(function (AvanceFase $record): ?string {
                        if (!$record->fecha_liberacion) {
                            return null;
                        }

                        $lines = [];
                        $lines[] = '📅 Liberada: ' . $record->fecha_liberacion->format('d/m/Y H:i');

                        // Buscar la fase anterior para obtener sus notas
                        $fasesConfiguradasIds = $record->programa->getFasesConfiguradasIds();
                        $fasesConfiguradas = Fase::whereIn('id', $fasesConfiguradasIds)
                            ->orderBy('orden', 'asc')
                            ->get();

                        $faseAnterior = $fasesConfiguradas->where('orden', '<', $record->fase->orden)
                            ->sortByDesc('orden')
                            ->first();

                        if ($faseAnterior) {
                            $avanceAnterior = AvanceFase::where('programa_id', $record->programa_id)
                                ->where('fase_id', $faseAnterior->id)
                                ->first();

                            if ($avanceAnterior && $avanceAnterior->notas_finalizacion) {
                                $lines[] = '';
                                $lines[] = '📝 Notas de ' . $faseAnterior->nombre . ':';
                                $lines[] = '─────────────────────';
                                $lines[] = $avanceAnterior->notas_finalizacion;
                            }
                        }

                        return implode("\n", $lines);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->html()
                    ->formatStateUsing(function (AvanceFase $record): ?string {
                        if (!$record->fecha_inicio) {
                            return null;
                        }

                        $fecha = $record->fecha_inicio->format('d/m/Y H:i');

                        // Agregar icono si tiene notas
                        return $record->notas ? $fecha . ' <span style="color: #9ca3af; opacity: 0.7;">📝</span>' : $fecha;
                    })
                    ->sortable()
                    ->placeholder('—')
                    ->tooltip(function (AvanceFase $record): ?string {
                        if (!$record->fecha_inicio) {
                            return null;
                        }

                        $lines = [];
                        $lines[] = '📅 Iniciada: ' . $record->fecha_inicio->format('d/m/Y H:i');

                        // Mostrar las notas si existen
                        if ($record->notas) {
                            $lines[] = '';
                            $lines[] = '📝 Notas:';
                            $lines[] = '─────────────────────';
                            $lines[] = $record->notas;
                        }

                        return implode("\n", $lines);
                    }),

                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Finalización')
                    ->html()
                    ->formatStateUsing(function (AvanceFase $record): ?string {
                        if (!$record->fecha_fin) {
                            return null;
                        }

                        $fecha = $record->fecha_fin->format('d/m/Y H:i');

                        // Agregar icono si tiene notas de finalización
                        return $record->notas_finalizacion ? $fecha . ' <span style="color: #9ca3af; opacity: 0.7;">📝</span>' : $fecha;
                    })
                    ->sortable()
                    ->placeholder('—')
                    ->tooltip(function (AvanceFase $record): ?string {
                        if (!$record->fecha_fin) {
                            return null;
                        }

                        $lines = [];
                        $lines[] = '📅 Finalizada: ' . $record->fecha_fin->format('d/m/Y H:i');

                        // Mostrar las notas de finalización de esta misma fase
                        if ($record->notas_finalizacion) {
                            $lines[] = '';
                            $lines[] = '📝 Notas de finalización:';
                            $lines[] = '─────────────────────';
                            $lines[] = $record->notas_finalizacion;
                        }

                        return implode("\n", $lines);
                    }),

                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(40)
                    ->tooltip(fn (AvanceFase $record): ?string => $record->notas)
                    ->placeholder('Sin notas')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'progress' => 'En Progreso',
                        'done' => 'Finalizado',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('iniciar')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->tooltip('Iniciar fase')
                    ->visible(fn (AvanceFase $record) => $record->estado === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Fase')
                    ->modalDescription(function (AvanceFase $record) {
                        // Obtener las fases configuradas para este programa
                        $fasesConfiguradasIds = $record->programa->getFasesConfiguradasIds();
                        $fasesConfiguradas = Fase::whereIn('id', $fasesConfiguradasIds)
                            ->orderBy('orden', 'asc')
                            ->get();

                        // Buscar la fase anterior DENTRO de las configuradas
                        $faseAnterior = $fasesConfiguradas->where('orden', '<', $record->fase->orden)
                            ->sortByDesc('orden')
                            ->first();

                        if ($faseAnterior) {
                            $avanceAnterior = AvanceFase::where('programa_id', $record->programa_id)
                                ->where('fase_id', $faseAnterior->id)
                                ->first();

                            if ($avanceAnterior && $avanceAnterior->notas_finalizacion) {
                                return '¿Deseas iniciar esta fase ahora? La fase anterior dejó las siguientes notas:';
                            }
                        }

                        return '¿Deseas iniciar esta fase ahora?';
                    })
                    ->modalSubmitActionLabel('Sí, iniciar')
                    ->successNotificationTitle('Fase iniciada exitosamente')
                    ->form(function (AvanceFase $record) {
                        $formFields = [];

                        // Obtener las fases configuradas para este programa
                        $fasesConfiguradasIds = $record->programa->getFasesConfiguradasIds();
                        $fasesConfiguradas = Fase::whereIn('id', $fasesConfiguradasIds)
                            ->orderBy('orden', 'asc')
                            ->get();

                        // Buscar la fase anterior DENTRO de las configuradas
                        $faseAnterior = $fasesConfiguradas->where('orden', '<', $record->fase->orden)
                            ->sortByDesc('orden')
                            ->first();

                        if ($faseAnterior) {
                            $avanceAnterior = AvanceFase::where('programa_id', $record->programa_id)
                                ->where('fase_id', $faseAnterior->id)
                                ->first();

                            if ($avanceAnterior && $avanceAnterior->notas_finalizacion) {
                                $formFields[] = Forms\Components\Placeholder::make('notas_fase_anterior')
                                    ->label("📝 Notas de la fase anterior ({$faseAnterior->nombre})")
                                    ->content($avanceAnterior->notas_finalizacion)
                                    ->columnSpanFull();
                            }
                        }

                        // Agregar campo de notas para el inicio
                        $formFields[] = Forms\Components\Textarea::make('notas')
                            ->label('Notas de inicio')
                            ->rows(3)
                            ->placeholder('Agrega notas o comentarios al iniciar esta fase...')
                            ->columnSpanFull();

                        return $formFields;
                    })
                    ->action(function (AvanceFase $record, array $data) {
                        // Obtener las fases configuradas para este programa
                        $fasesConfiguradasIds = $record->programa->getFasesConfiguradasIds();
                        $fasesConfiguradas = Fase::whereIn('id', $fasesConfiguradasIds)
                            ->orderBy('orden', 'asc')
                            ->get();

                        // Buscar la fase anterior DENTRO de las configuradas
                        $faseAnterior = $fasesConfiguradas->where('orden', '<', $record->fase->orden)
                            ->sortByDesc('orden')
                            ->first();

                        if ($faseAnterior) {
                            $avanceAnterior = AvanceFase::where('programa_id', $record->programa_id)
                                ->where('fase_id', $faseAnterior->id)
                                ->first();

                            if (!$avanceAnterior || $avanceAnterior->estado !== 'done') {
                                Notification::make()
                                    ->danger()
                                    ->title('No se puede iniciar')
                                    ->body("La fase anterior configurada ({$faseAnterior->nombre}) debe estar completada antes de iniciar esta fase.")
                                    ->duration(6000)
                                    ->send();
                                return;
                            }
                        }

                        $record->update([
                            'estado' => 'progress',
                            'fecha_inicio' => now(),
                            'notas' => $data['notas'] ?? $record->notas,
                            'responsable_id' => Auth::id(), // Asignar el usuario que inicia
                        ]);
                    }),

                Tables\Actions\Action::make('finalizar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->tooltip('Finalizar fase')
                    ->visible(fn (AvanceFase $record) => $record->estado === 'progress')
                    ->requiresConfirmation()
                    ->modalHeading('Finalizar Fase')
                    ->modalDescription('¿Estás seguro de marcar esta fase como finalizada?')
                    ->modalSubmitActionLabel('Sí, finalizar')
                    ->successNotificationTitle('Fase completada exitosamente')
                    ->form([
                        Forms\Components\Textarea::make('notas_finalizacion')
                            ->label('Notas para la siguiente fase')
                            ->rows(3)
                            ->placeholder('Agrega comentarios o instrucciones para la fase siguiente...'),
                    ])
                    ->action(function (AvanceFase $record, array $data) {
                        $record->update([
                            'estado' => 'done',
                            'fecha_fin' => now(),
                            'notas_finalizacion' => $data['notas_finalizacion'] ?? null,
                            'responsable_id' => Auth::id(), // Asignar el usuario que finaliza
                        ]);
                    }),

                Tables\Actions\Action::make('liberar_siguiente')
                    ->icon(function (AvanceFase $record) {
                        // Si ya tiene fecha de liberación, mostrar icono de check
                        if ($record->fecha_liberacion) {
                            return 'heroicon-o-check-badge';
                        }
                        return 'heroicon-o-arrow-right-circle';
                    })
                    ->color(function (AvanceFase $record) {
                        // Si ya tiene fecha de liberación, mostrar en verde
                        if ($record->fecha_liberacion) {
                            return 'success';
                        }
                        return 'warning';
                    })
                    ->label(function (AvanceFase $record) {
                        if ($record->fecha_liberacion) {
                            return 'Liberada';
                        }
                        return 'Liberar';
                    })
                    ->tooltip(function (AvanceFase $record): ?string {
                        if ($record->fecha_liberacion) {
                            $lines = [];
                            $lines[] = '📅 Liberada: ' . $record->fecha_liberacion->format('d/m/Y H:i');

                            // Buscar la fase anterior para obtener sus notas
                            $fasesConfiguradasIds = $record->programa->getFasesConfiguradasIds();
                            $fasesConfiguradas = Fase::whereIn('id', $fasesConfiguradasIds)
                                ->orderBy('orden', 'asc')
                                ->get();

                            $faseAnterior = $fasesConfiguradas->where('orden', '<', $record->fase->orden)
                                ->sortByDesc('orden')
                                ->first();

                            if ($faseAnterior) {
                                $avanceAnterior = AvanceFase::where('programa_id', $record->programa_id)
                                    ->where('fase_id', $faseAnterior->id)
                                    ->first();

                                if ($avanceAnterior && $avanceAnterior->notas_finalizacion) {
                                    $lines[] = '';
                                    $lines[] = '📝 Notas de ' . $faseAnterior->nombre . ':';
                                    $lines[] = '─────────────────────';
                                    $lines[] = $avanceAnterior->notas_finalizacion;
                                }
                            }

                            return implode("\n", $lines);
                        }
                        return 'Liberar siguiente fase';
                    })
                    ->disabled(fn (AvanceFase $record) => $record->fecha_liberacion !== null)
                    ->visible(function (AvanceFase $record) {
                        // Solo visible si está finalizado
                        if ($record->estado !== 'done') {
                            return false;
                        }

                        // Usar el método del programa para obtener la siguiente fase según el perfil
                        $siguienteFaseData = $record->programa->getSiguienteFase($record->fase_id);

                        // Ocultar si no hay siguiente fase
                        if (!$siguienteFaseData) {
                            return false;
                        }

                        // SIEMPRE mostrar el botón si hay siguiente fase
                        return true;
                    })
                    ->requiresConfirmation(fn (AvanceFase $record) => $record->fecha_liberacion === null)
                    ->modalHeading('Liberar Siguiente Fase')
                    ->modalDescription(function (AvanceFase $record) {
                        // Usar el método del programa para obtener la siguiente fase según el perfil
                        $siguienteFaseData = $record->programa->getSiguienteFase($record->fase_id);

                        if ($siguienteFaseData && isset($siguienteFaseData['fase'])) {
                            $siguienteFase = $siguienteFaseData['fase'];
                            $areaId = $siguienteFaseData['area_id'];
                            $area = \App\Models\Area::find($areaId);
                            $areaInfo = $area ? " (Área: {$area->nombre})" : '';
                            return "📋 Fase actual: {$record->fase->nombre}\n\n⏭️ Será liberada a: {$siguienteFase->nombre}{$areaInfo}\n\n¿Deseas continuar? Los usuarios responsables serán notificados.";
                        }

                        return '¿Deseas liberar la siguiente fase del proceso?';
                    })
                    ->modalSubmitActionLabel('Sí, liberar fase')
                    ->action(function (AvanceFase $record) {
                        // Si ya está liberada, no hacer nada
                        if ($record->fecha_liberacion) {
                            return;
                        }

                        $faseActual = $record->fase;

                        // Usar el método del programa para obtener la siguiente fase según el perfil
                        $siguienteFaseData = $record->programa->getSiguienteFase($record->fase_id);

                        if (!$siguienteFaseData || !isset($siguienteFaseData['fase'])) {
                            Notification::make()
                                ->warning()
                                ->title('No hay siguiente fase')
                                ->body('Esta es la última fase configurada para este programa.')
                                ->send();
                            return;
                        }

                        $siguienteFase = $siguienteFaseData['fase'];
                        $areaIdSiguiente = $siguienteFaseData['area_id'];

                        // ✨ Marcar fecha de liberación en la fase ACTUAL
                        $record->update([
                            'fecha_liberacion' => now(),
                        ]);

                        // 🔹 AUTO-CREAR siguiente avance de fase
                        // Verificar si ya existe un avance para esta fase del programa
                        $avanceExistente = AvanceFase::where('programa_id', $record->programa_id)
                            ->where('fase_id', $siguienteFase->id)
                            ->first();

                        if (!$avanceExistente) {
                            try {
                                if (!$areaIdSiguiente) {
                                    \Log::warning("No se pudo determinar área para fase: {$siguienteFase->nombre}");
                                }

                                // Crear el siguiente avance automáticamente asignándolo al área del perfil
                                $nuevoAvance = AvanceFase::create([
                                    'programa_id' => $record->programa_id,
                                    'fase_id' => $siguienteFase->id,
                                    'area_id' => $areaIdSiguiente,
                                    'responsable_id' => null, // Sin responsable específico, visible para toda el área
                                    'estado' => 'pending',
                                    'activo' => true,
                                ]);

                                \Log::info("Avance creado exitosamente para programa {$record->programa_id}, fase {$siguienteFase->nombre}, area_id: {$areaIdSiguiente}");
                            } catch (\Exception $e) {
                                \Log::error("Error al crear avance de fase: " . $e->getMessage());

                                Notification::make()
                                    ->danger()
                                    ->title('Error al crear siguiente fase')
                                    ->body("Ocurrió un error al crear el avance para la fase {$siguienteFase->nombre}. Error: " . $e->getMessage())
                                    ->send();

                                return;
                            }
                        }

                        // Buscar usuarios del área de la siguiente fase para notificar
                        $usuariosNotificar = collect();

                        if ($areaIdSiguiente) {
                            $usuariosNotificar = User::where('area_id', $areaIdSiguiente)->get();
                        }

                        // Si no hay usuarios en el área, notificar a administradores
                        if ($usuariosNotificar->isEmpty()) {
                            $usuariosNotificar = User::role('Administrador')->get();
                        }

                        // Enviar notificaciones
                        foreach ($usuariosNotificar as $usuario) {
                            $usuario->notify(new FaseLiberada(
                                $record->programa,
                                $faseActual,
                                $siguienteFase
                            ));
                        }

                        $area = \App\Models\Area::find($areaIdSiguiente);
                        $areaNotificada = $area ? $area->nombre : 'sin área asignada';

                        Notification::make()
                            ->success()
                            ->title('Fase liberada exitosamente')
                            ->body("Se ha notificado a los usuarios del área {$areaNotificada} para la fase: {$siguienteFase->nombre}. El avance ha sido creado automáticamente.")
                            ->send();
                    }),

                Tables\Actions\Action::make('deshacer')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->label('')
                    ->tooltip('Deshacer progreso')
                    ->visible(function (AvanceFase $record) {
                        // Solo visible si está en progreso o finalizado
                        if (!in_array($record->estado, ['progress', 'done'])) {
                            return false;
                        }

                        // Verificar que NO haya fases posteriores en progreso o finalizadas
                        $hayFasesPosterioresActivas = AvanceFase::where('programa_id', $record->programa_id)
                            ->whereHas('fase', function ($query) use ($record) {
                                $query->where('orden', '>', $record->fase->orden);
                            })
                            ->whereIn('estado', ['progress', 'done'])
                            ->exists();

                        // Solo mostrar si NO hay fases posteriores activas
                        return !$hayFasesPosterioresActivas;
                    })
                    ->action(function (AvanceFase $record) {
                        // Obtener todas las fases posteriores del mismo programa
                        $fasesPosteriores = AvanceFase::where('programa_id', $record->programa_id)
                            ->whereHas('fase', function ($query) use ($record) {
                                $query->where('orden', '>', $record->fase->orden);
                            })
                            ->with('fase')
                            ->get();

                        // Contador de fases afectadas
                        $fasesAfectadas = 0;

                        // Deshacer todas las fases posteriores primero (cascada)
                        foreach ($fasesPosteriores as $fasePost) {
                            if ($fasePost->estado !== 'pending') {
                                $fasePost->update([
                                    'estado' => 'pending',
                                    'fecha_inicio' => null,
                                    'fecha_fin' => null,
                                ]);
                                $fasesAfectadas++;
                            }
                        }

                        // Ahora deshacer la fase actual
                        if ($record->estado === 'done') {
                            // Si está finalizado, volver a "en progreso"
                            $record->update([
                                'estado' => 'progress',
                                'fecha_fin' => null,
                            ]);

                            $mensaje = 'La fase ha vuelto a estado "En Progreso"';
                            if ($fasesAfectadas > 0) {
                                $mensaje .= " y se han restablecido {$fasesAfectadas} fase(s) posterior(es) a estado Pendiente.";
                            }

                            Notification::make()
                                ->success()
                                ->title('Fase deshecha')
                                ->body($mensaje)
                                ->duration(5000)
                                ->send();
                        } elseif ($record->estado === 'progress') {
                            // Si está en progreso, volver a "pendiente"
                            $record->update([
                                'estado' => 'pending',
                                'fecha_inicio' => null,
                            ]);

                            $mensaje = 'La fase ha vuelto a estado "Pendiente"';
                            if ($fasesAfectadas > 0) {
                                $mensaje .= " y se han restablecido {$fasesAfectadas} fase(s) posterior(es) a estado Pendiente.";
                            }

                            Notification::make()
                                ->success()
                                ->title('Fase deshecha')
                                ->body($mensaje)
                                ->duration(5000)
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Deshacer Progreso')
                    ->modalDescription(function (AvanceFase $record) {
                        // Contar fases posteriores que serán afectadas
                        $fasesPosteriores = AvanceFase::where('programa_id', $record->programa_id)
                            ->whereHas('fase', function ($query) use ($record) {
                                $query->where('orden', '>', $record->fase->orden);
                            })
                            ->where('estado', '!=', 'pending')
                            ->count();

                        $descripcion = '⚠️ ¿Estás seguro de deshacer el progreso? La fase retrocederá un paso.';

                        if ($fasesPosteriores > 0) {
                            $descripcion .= "\n\n🔄 IMPORTANTE: También se restablecerán automáticamente {$fasesPosteriores} fase(s) posterior(es) a estado Pendiente para mantener la integridad del proceso.";
                        }

                        return $descripcion;
                    })
                    ->modalSubmitActionLabel('Sí, deshacer'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->label('')
                    ->tooltip('Eliminar progreso completamente')
                    ->visible(fn () => Auth::user()?->hasRole('Administrador') ?? false)
                    ->modalHeading('Eliminar Progreso Completamente')
                    ->modalDescription('⚠️ ADVERTENCIA: Esta acción eliminará permanentemente este avance de fase. Solo los administradores pueden realizar esta acción.')
                    ->modalSubmitActionLabel('Sí, eliminar permanentemente')
                    ->successNotificationTitle('Progreso eliminado')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Progreso eliminado')
                            ->body('El avance de fase ha sido eliminado permanentemente.')
                    ),

                Tables\Actions\Action::make('editar_notas')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->label('')
                    ->tooltip('Editar notas')
                    ->modalHeading('Editar Notas')
                    ->modalSubmitActionLabel('Guardar')
                    ->successNotificationTitle('Notas actualizadas exitosamente')
                    ->form([
                        Forms\Components\Textarea::make('notas')
                            ->label('Notas')
                            ->rows(4)
                            ->default(fn (AvanceFase $record) => $record->notas),
                    ])
                    ->action(function (AvanceFase $record, array $data) {
                        $record->update(['notas' => $data['notas']]);
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No tienes fases asignadas')
            ->emptyStateDescription('Cuando te asignen fases, aparecerán aquí.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->poll('30s'); // Auto-actualización cada 30 segundos
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\MisFasesStats::class,
        ];
    }
}
