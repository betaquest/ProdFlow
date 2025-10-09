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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('programa_id')
                    ->label('Programa')
                    ->relationship('programa', 'nombre')
                    ->required(),
                Forms\Components\Select::make('fase_id')
                    ->label('Fase')
                    ->relationship('fase', 'nombre')
                    ->required(),
                Forms\Components\Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'name')
                    ->nullable(),
                Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'progress' => 'En proceso',
                        'done' => 'Finalizado',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('fecha_inicio')->label('Inicio'),
                Forms\Components\DateTimePicker::make('fecha_fin')->label('Finalización'),
                Forms\Components\Textarea::make('notas')->label('Notas')->rows(3),
                Forms\Components\Toggle::make('activo')->label('Activo')->default(true),
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
                Tables\Columns\TextColumn::make('responsable.name')->label('Responsable'),
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
                        ->label('Iniciar')
                        ->icon('heroicon-o-play')
                        ->color('info')
                        ->visible(fn (AvanceFase $record) => $record->estado === 'pending')
                        ->action(function (AvanceFase $record) {
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
                        ->label('Finalizar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (AvanceFase $record) => $record->estado === 'progress')
                        ->form([
                            Forms\Components\Textarea::make('notas')
                                ->label('Notas finales (opcional)')
                                ->rows(3),
                        ])
                        ->action(function (AvanceFase $record, array $data) {
                            $record->update([
                                'estado' => 'done',
                                'fecha_fin' => now(),
                                'notas' => $data['notas'] ?? $record->notas,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('¡Fase completada!')
                                ->body('La fase ha sido finalizada exitosamente.')
                                ->send();
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('liberar_fase')
                        ->label('Liberar Siguiente')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('warning')
                        ->visible(function (AvanceFase $record) {
                            // Solo visible si está finalizado
                            if ($record->estado !== 'done') {
                                return false;
                            }

                            // Verificar si existe siguiente fase
                            $siguienteFase = $record->fase->siguienteFase();
                            if (!$siguienteFase) {
                                return false;
                            }

                            // Verificar si la siguiente fase ya fue iniciada o finalizada
                            $avanceSiguiente = AvanceFase::where('programa_id', $record->programa_id)
                                ->where('fase_id', $siguienteFase->id)
                                ->first();

                            // Solo mostrar si NO existe o si está en estado 'pending'
                            return !$avanceSiguiente || $avanceSiguiente->estado === 'pending';
                        })
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

                    Tables\Actions\EditAction::make(),
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
