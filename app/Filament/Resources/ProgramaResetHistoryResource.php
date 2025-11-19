<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramaResetHistoryResource\Pages;
use App\Filament\Resources\ProgramaResetHistoryResource\RelationManagers;
use App\Models\ProgramaResetHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgramaResetHistoryResource extends Resource
{
    protected static ?string $model = ProgramaResetHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Historial de Reinicios';

    protected static ?string $navigationGroup = 'Producción';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Historial de Reinicio';

    protected static ?string $pluralModelLabel = 'Historial de Reinicios';

    public static function canCreate(): bool
    {
        return false; // No se pueden crear manualmente
    }

    public static function canEdit($record): bool
    {
        return false; // Solo lectura
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('programas.eliminar'); // Solo quien puede eliminar programas
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('programas.ver_historial_reinicios');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Reinicio')
                    ->schema([
                        Forms\Components\TextInput::make('programa_nombre')
                            ->label('Programa')
                            ->disabled(),
                        Forms\Components\TextInput::make('ejecutadoPor.name')
                            ->label('Ejecutado por')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_avances_eliminados')
                            ->label('Avances eliminados')
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('Fecha de reinicio')
                            ->disabled(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Motivo')
                    ->schema([
                        Forms\Components\Textarea::make('motivo')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Datos de Respaldo')
                    ->schema([
                        Forms\Components\Placeholder::make('respaldo_info')
                            ->content(fn ($record) => view('filament.components.programa-reset-backup', ['datos' => $record->datos_respaldo]))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('programa_nombre')
                    ->label('Programa')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->programa->proyecto?->nombre ?? 'Programa eliminado'),
                Tables\Columns\TextColumn::make('ejecutadoPor.name')
                    ->label('Ejecutado por')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_avances_eliminados')
                    ->label('Avances Eliminados')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Reinicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->motivo)
                    ->wrap()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ejecutado_por')
                    ->label('Ejecutado por')
                    ->relationship('ejecutadoPor', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('restaurar')
                    ->label('Restaurar Avances')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Restaurar Avances del Programa')
                    ->modalDescription(fn ($record) => "¿Estás seguro que deseas restaurar los {$record->total_avances_eliminados} avances eliminados del programa '{$record->programa_nombre}'?")
                    ->modalSubmitActionLabel('Sí, restaurar')
                    ->visible(fn () => auth()->user()->can('programas.restaurar_avances'))
                    ->action(function (ProgramaResetHistory $record) {
                        return static::restaurarAvances($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('programas.eliminar')),
                ]),
            ]);
    }

    public static function restaurarAvances(ProgramaResetHistory $record)
    {
        try {
            \DB::beginTransaction();

            $programa = $record->programa;

            if (!$programa) {
                \Filament\Notifications\Notification::make()
                    ->danger()
                    ->title('Error')
                    ->body('El programa asociado ya no existe.')
                    ->send();
                return;
            }

            // Restaurar cada avance del respaldo
            foreach ($record->datos_respaldo as $avanceData) {
                \App\Models\AvanceFase::create([
                    'programa_id' => $programa->id,
                    'fase_id' => $avanceData['fase_id'],
                    'responsable_id' => $avanceData['responsable_id'],
                    'estado' => $avanceData['estado'],
                    'fecha_inicio' => $avanceData['fecha_inicio'],
                    'fecha_fin' => $avanceData['fecha_fin'],
                    'notas' => $avanceData['notas'] ?? null,
                    'activo' => true,
                ]);
            }

            \DB::commit();

            \Filament\Notifications\Notification::make()
                ->success()
                ->title('Avances restaurados')
                ->body("Se restauraron exitosamente {$record->total_avances_eliminados} avances del programa '{$record->programa_nombre}'.")
                ->send();

        } catch (\Exception $e) {
            \DB::rollBack();

            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Error al restaurar')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->send();
        }
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
            'index' => Pages\ListProgramaResetHistories::route('/'),
            'view' => Pages\ViewProgramaResetHistory::route('/{record}'),
        ];
    }
}
