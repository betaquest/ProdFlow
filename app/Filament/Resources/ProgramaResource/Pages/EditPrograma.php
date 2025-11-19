<?php

namespace App\Filament\Resources\ProgramaResource\Pages;

use App\Filament\Resources\ProgramaResource;
use App\Models\ProgramaResetHistory;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditPrograma extends EditRecord
{
    protected static string $resource = ProgramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reiniciar_programa')
                ->label('Reiniciar Programa')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reiniciar Programa')
                ->modalDescription('Esta acción eliminará TODO el progreso del programa. Esta operación es irreversible.')
                ->modalSubmitActionLabel('Sí, reiniciar programa')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->form([
                    \Filament\Forms\Components\Textarea::make('motivo')
                        ->label('Motivo del reinicio')
                        ->required()
                        ->placeholder('Describe por qué es necesario reiniciar este programa...')
                        ->rows(3),
                    \Filament\Forms\Components\TextInput::make('confirmacion')
                        ->label('Escribe "REINICIAR" para confirmar')
                        ->required()
                        ->placeholder('REINICIAR')
                        ->helperText('Debes escribir exactamente la palabra REINICIAR en mayúsculas.')
                        ->rule('in:REINICIAR'),
                ])
                ->visible(fn () => $this->puedeReiniciarPrograma())
                ->action(function (array $data) {
                    $this->reiniciarPrograma($data['motivo']);
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function puedeReiniciarPrograma(): bool
    {
        $user = auth()->user();
        $programa = $this->record;

        // Verificar si el usuario tiene el permiso 'programas.reiniciar'
        if (!$user->can('programas.reiniciar')) {
            return false;
        }

        // Verificar si el usuario es el creador del programa
        if ($programa->creado_por && $programa->creado_por !== $user->id) {
            return false;
        }

        return true;
    }

    protected function reiniciarPrograma(string $motivo): void
    {
        $programa = $this->record;
        $user = auth()->user();

        try {
            DB::beginTransaction();

            // Crear respaldo de los avances antes de eliminar
            $avancesRespaldo = $programa->avances()
                ->with(['fase', 'responsable'])
                ->get()
                ->map(function ($avance) {
                    return [
                        'fase_id' => $avance->fase_id,
                        'fase_nombre' => $avance->fase->nombre ?? 'Desconocida',
                        'responsable_id' => $avance->responsable_id,
                        'responsable_nombre' => $avance->responsable->name ?? 'Sin asignar',
                        'estado' => $avance->estado,
                        'fecha_inicio' => $avance->fecha_inicio?->toDateTimeString(),
                        'fecha_fin' => $avance->fecha_fin?->toDateTimeString(),
                        'notas' => $avance->notas,
                        'created_at' => $avance->created_at->toDateTimeString(),
                        'updated_at' => $avance->updated_at->toDateTimeString(),
                    ];
                })
                ->toArray();

            $totalAvances = count($avancesRespaldo);

            // Guardar historial del reinicio
            ProgramaResetHistory::create([
                'programa_id' => $programa->id,
                'programa_nombre' => $programa->nombre,
                'ejecutado_por' => $user->id,
                'datos_respaldo' => $avancesRespaldo,
                'total_avances_eliminados' => $totalAvances,
                'motivo' => $motivo,
            ]);

            // Soft delete de todos los avances
            $programa->avances()->delete();

            DB::commit();

            Notification::make()
                ->success()
                ->title('Programa reiniciado')
                ->body("Se eliminaron {$totalAvances} avances. Un respaldo fue guardado en el historial.")
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->danger()
                ->title('Error al reiniciar programa')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->send();
        }
    }
}
