<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AvanceFaseResource\Pages;
use App\Models\AvanceFase;
use App\Models\Fase;
use App\Models\User;
use App\Notifications\FaseLiberada;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class AvanceFaseResource extends Resource
{
    protected static ?string $model = AvanceFase::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Avances de Fase';

    /**
     * Restringir acceso solo a Administradores
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->hasRole('Administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('programa_id')
                    ->label('Programa')
                    ->relationship('programa', 'nombre')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('fase_id')
                    ->label('Fase')
                    ->relationship('fase', 'nombre')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'name')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'progress' => 'En proceso',
                        'done' => 'Finalizado',
                    ])
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('fecha_inicio')
                    ->label('Inicio')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('fecha_fin')
                    ->label('Finalización')
                    ->columnSpanFull(),
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
            ->defaultSort('fecha_inicio', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('programa.nombre')->label('Programa')->sortable(),
                Tables\Columns\TextColumn::make('fase.nombre')->label('Fase')->sortable(),
                Tables\Columns\TextColumn::make('area.nombre')
                    ->label('Área')
                    ->badge()
                    ->color('info')
                    ->placeholder('Sin área'),
                Tables\Columns\TextColumn::make('responsable.name')
                    ->label('Responsable')
                    ->placeholder('Sin asignar'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'progress',
                        'success' => 'done',
                    ])
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('fecha_inicio')->label('Inicio')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('fecha_fin')->label('Fin')->dateTime('d/m/Y H:i'),
                Tables\Columns\IconColumn::make('activo')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('notas')->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'progress' => 'En proceso',
                        'done' => 'Finalizado',
                    ]),
                Tables\Filters\TernaryFilter::make('activo')->label('Activo')->boolean(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('iniciar')
                        ->icon('heroicon-o-play')
                        ->color('info')
                        ->tooltip('Iniciar fase')
                        ->visible(fn (AvanceFase $record) => $record->estado === 'pending')
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
                        ->form(function (AvanceFase $record) {
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
                                    return [
                                        Forms\Components\Placeholder::make('notas_fase_anterior')
                                            ->label("📝 Notas de la fase anterior ({$faseAnterior->nombre})")
                                            ->content($avanceAnterior->notas_finalizacion)
                                            ->columnSpanFull(),
                                    ];
                                }
                            }

                            return [];
                        })
                        ->action(function (AvanceFase $record) {
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
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Fase iniciada')
                                ->body('La fase ha sido marcada como "En Progreso"')
                                ->send();
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('finalizar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->tooltip('Finalizar fase')
                        ->visible(fn (AvanceFase $record) => $record->estado === 'progress')
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
                            ]);

                            Notification::make()
                                ->success()
                                ->title('¡Fase completada!')
                                ->body('La fase ha sido finalizada exitosamente.')
                                ->send();
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('liberar_fase')
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
                        ->tooltip(function (AvanceFase $record) {
                            if ($record->fecha_liberacion) {
                                return 'Fase liberada el ' . $record->fecha_liberacion->format('d/m/Y H:i');
                            }
                            return 'Liberar siguiente fase';
                        })
                        ->visible(function (AvanceFase $record) {
                            // Solo visible si está finalizado
                            if ($record->estado !== 'done') {
                                return false;
                            }

                            // Obtener las fases configuradas para este programa
                            $fasesConfiguradasIds = $record->programa->getFasesConfiguradasIds();
                            $fasesConfiguradas = Fase::whereIn('id', $fasesConfiguradasIds)
                                ->orderBy('orden', 'asc')
                                ->get();

                            // Buscar la siguiente fase DENTRO de las configuradas
                            $siguienteFase = $fasesConfiguradas->where('orden', '>', $record->fase->orden)->first();

                            // Ocultar si no hay siguiente fase
                            if (!$siguienteFase) {
                                return false;
                            }

                            // SIEMPRE mostrar el botón si hay siguiente fase
                            return true;
                        })
                        ->disabled(fn (AvanceFase $record) => $record->fecha_liberacion !== null)
                        ->action(function (AvanceFase $record) {
                            $faseActual = $record->fase;

                            // Obtener las fases configuradas para este programa
                            $fasesConfiguradasIds = $record->programa->getFasesConfiguradasIds();
                            $fasesConfiguradas = Fase::whereIn('id', $fasesConfiguradasIds)
                                ->orderBy('orden', 'asc')
                                ->get();

                            // Buscar la siguiente fase DENTRO de las configuradas
                            $siguienteFase = $fasesConfiguradas->where('orden', '>', $faseActual->orden)->first();

                            if (!$siguienteFase) {
                                Notification::make()
                                    ->warning()
                                    ->title('No hay siguiente fase')
                                    ->body('Esta es la última fase configurada para este programa.')
                                    ->send();
                                return;
                            }

                            // 🔹 AUTO-CREAR siguiente avance de fase
                            $avanceExistente = AvanceFase::where('programa_id', $record->programa_id)
                                ->where('fase_id', $siguienteFase->id)
                                ->first();

                            if (!$avanceExistente) {
                                $rolNombre = $siguienteFase->nombre;
                                $primerUsuarioRol = User::role($rolNombre)->first();

                                AvanceFase::create([
                                    'programa_id' => $record->programa_id,
                                    'fase_id' => $siguienteFase->id,
                                    'responsable_id' => $primerUsuarioRol?->id,
                                    'estado' => 'pending',
                                    'activo' => true,
                                ]);
                            }

                            // Notificar usuarios
                            $usuariosNotificar = User::role($siguienteFase->nombre)->get();

                            if ($usuariosNotificar->isEmpty()) {
                                $usuariosNotificar = User::role('Administrador')->get();
                            }

                            foreach ($usuariosNotificar as $usuario) {
                                $usuario->notify(new FaseLiberada(
                                    $record->programa,
                                    $faseActual,
                                    $siguienteFase
                                ));
                            }

                            Notification::make()
                                ->success()
                                ->title('Fase liberada exitosamente')
                                ->body("Se ha notificado a los usuarios de la fase: {$siguienteFase->nombre}. El avance ha sido creado automáticamente.")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Liberar Siguiente Fase')
                        ->modalDescription('¿Estás seguro de liberar la siguiente fase? Se notificará a los usuarios responsables.')
                        ->modalSubmitActionLabel('Sí, liberar'),

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
                        ->label('')
                        ->tooltip('Eliminar progreso completamente')
                        ->visible(fn () => auth()->user()?->hasRole('Administrador') ?? false)
                        ->modalHeading('Eliminar Progreso Completamente')
                        ->modalDescription('⚠️ ADVERTENCIA: Esta acción eliminará permanentemente este avance de fase. Solo los administradores pueden realizar esta acción.')
                        ->modalSubmitActionLabel('Sí, eliminar permanentemente'),

                    Tables\Actions\EditAction::make()
                        ->tooltip('Editar avance'),
                ]),
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
            'index' => Pages\ListAvanceFases::route('/'),
            'create' => Pages\CreateAvanceFase::route('/create'),
            'edit' => Pages\EditAvanceFase::route('/{record}/edit'),
        ];
    }
}
