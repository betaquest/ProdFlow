<?php

namespace App\Filament\Pages;

use App\Models\AvanceFase;
use App\Models\Fase;
use App\Models\User;
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
use Illuminate\Support\Facades\Auth;

class MisFases extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.mis-fases';

    protected static ?string $navigationLabel = 'Proceso';

    protected static ?string $title = 'Mi Proceso de Trabajo';

    protected static ?int $navigationSort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AvanceFase::query()
                    ->where('responsable_id', Auth::id())
                    ->with(['programa.proyecto.cliente', 'fase'])
            )
            ->defaultSort('created_at', 'desc')
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

                Tables\Columns\TextColumn::make('fase.nombre')
                    ->label('Fase')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'progress' => 'En Progreso',
                        'done' => 'Finalizado',
                        default => $state,
                    })
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'progress',
                        'success' => 'done',
                    ])
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'progress' => 'heroicon-o-arrow-path',
                        'done' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Finalización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(40)
                    ->tooltip(fn (AvanceFase $record): ?string => $record->notas)
                    ->placeholder('Sin notas'),
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
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (AvanceFase $record) => $record->estado === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Fase')
                    ->modalDescription('¿Deseas iniciar esta fase ahora?')
                    ->modalSubmitActionLabel('Sí, iniciar')
                    ->successNotificationTitle('Fase iniciada exitosamente')
                    ->action(function (AvanceFase $record) {
                        $record->update([
                            'estado' => 'progress',
                            'fecha_inicio' => now(),
                        ]);
                    }),

                Tables\Actions\Action::make('finalizar')
                    ->label('Finalizar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AvanceFase $record) => $record->estado === 'progress')
                    ->requiresConfirmation()
                    ->modalHeading('Finalizar Fase')
                    ->modalDescription('¿Estás seguro de marcar esta fase como finalizada?')
                    ->modalSubmitActionLabel('Sí, finalizar')
                    ->successNotificationTitle('Fase completada exitosamente')
                    ->form([
                        Forms\Components\Textarea::make('notas')
                            ->label('Notas finales (opcional)')
                            ->rows(3)
                            ->placeholder('Agrega comentarios sobre esta fase...'),
                    ])
                    ->action(function (AvanceFase $record, array $data) {
                        $record->update([
                            'estado' => 'done',
                            'fecha_fin' => now(),
                            'notas' => $data['notas'] ?? $record->notas,
                        ]);
                    }),

                Tables\Actions\Action::make('liberar_siguiente')
                    ->label('Liberar Siguiente')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn (AvanceFase $record) => $record->estado === 'done')
                    ->requiresConfirmation()
                    ->modalHeading('Liberar Siguiente Fase')
                    ->modalDescription('¿Deseas liberar la siguiente fase del proceso? Los usuarios responsables serán notificados.')
                    ->modalSubmitActionLabel('Sí, liberar fase')
                    ->action(function (AvanceFase $record) {
                        $faseActual = $record->fase;
                        $siguienteFase = $faseActual->siguienteFase();

                        if (!$siguienteFase) {
                            Notification::make()
                                ->warning()
                                ->title('No hay siguiente fase')
                                ->body('Esta es la última fase del proceso.')
                                ->send();
                            return;
                        }

                        // 🔹 AUTO-CREAR siguiente avance de fase
                        // Verificar si ya existe un avance para esta fase del programa
                        $avanceExistente = AvanceFase::where('programa_id', $record->programa_id)
                            ->where('fase_id', $siguienteFase->id)
                            ->first();

                        if (!$avanceExistente) {
                            // Buscar primer usuario con el rol de la siguiente fase
                            $rolNombre = $siguienteFase->nombre;
                            $primerUsuarioRol = User::role($rolNombre)->first();

                            // Crear el siguiente avance automáticamente
                            $nuevoAvance = AvanceFase::create([
                                'programa_id' => $record->programa_id,
                                'fase_id' => $siguienteFase->id,
                                'responsable_id' => $primerUsuarioRol?->id, // Asigna primer usuario del rol o null
                                'estado' => 'pending',
                                'activo' => true,
                            ]);
                        }

                        // Buscar usuarios con el rol de la siguiente fase para notificar
                        $usuariosNotificar = User::role($siguienteFase->nombre)->get();

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

                        Notification::make()
                            ->success()
                            ->title('Fase liberada exitosamente')
                            ->body("Se ha notificado a los usuarios de la fase: {$siguienteFase->nombre}. El avance ha sido creado automáticamente.")
                            ->send();
                    }),

                Tables\Actions\Action::make('editar_notas')
                    ->label('Editar Notas')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
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
