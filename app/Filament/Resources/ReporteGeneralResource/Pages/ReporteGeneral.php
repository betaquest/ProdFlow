<?php

namespace App\Filament\Resources\ReporteGeneralResource\Pages;

use App\Filament\Resources\ReporteGeneralResource;
use App\Models\AvanceFase;
use App\Models\Cliente;
use App\Models\Fase;
use App\Models\Programa;
use App\Models\Proyecto;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteGeneralExport;

class ReporteGeneral extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ReporteGeneralResource::class;

    protected static string $view = 'filament.resources.reporte-general-resource.pages.reporte-general';

    protected static ?string $title = 'Reportes Generales';

    public ?array $data = [];
    public ?array $resultados = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtros de Búsqueda')
                    ->schema([
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(Cliente::pluck('nombre', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('proyecto_id', null)),

                        Select::make('proyecto_id')
                            ->label('Proyecto')
                            ->options(function (callable $get) {
                                $clienteId = $get('cliente_id');
                                if (!$clienteId) {
                                    return Proyecto::pluck('nombre', 'id');
                                }
                                return Proyecto::where('cliente_id', $clienteId)->pluck('nombre', 'id');
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('programa_id', null)),

                        Select::make('programa_id')
                            ->label('Programa')
                            ->options(function (callable $get) {
                                $proyectoId = $get('proyecto_id');
                                if (!$proyectoId) {
                                    return Programa::pluck('nombre', 'id');
                                }
                                return Programa::where('proyecto_id', $proyectoId)->pluck('nombre', 'id');
                            })
                            ->searchable()
                            ->reactive(),

                        Select::make('fase_id')
                            ->label('Fase')
                            ->options(Fase::where('activo', true)->orderBy('orden')->pluck('nombre', 'id'))
                            ->searchable(),

                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de Inicio (Desde)')
                            ->native(false),

                        DatePicker::make('fecha_fin')
                            ->label('Fecha de Fin (Hasta)')
                            ->native(false),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function generarReporte(): void
    {
        $data = $this->form->getState();

        $query = AvanceFase::with([
            'programa.proyecto.cliente',
            'programa',
            'fase'
        ]);

        // Aplicar filtros
        if (!empty($data['cliente_id'])) {
            $query->whereHas('programa.proyecto', function ($q) use ($data) {
                $q->where('cliente_id', $data['cliente_id']);
            });
        }

        if (!empty($data['proyecto_id'])) {
            $query->whereHas('programa', function ($q) use ($data) {
                $q->where('proyecto_id', $data['proyecto_id']);
            });
        }

        if (!empty($data['programa_id'])) {
            $query->where('programa_id', $data['programa_id']);
        }

        if (!empty($data['fase_id'])) {
            $query->where('fase_id', $data['fase_id']);
        }

        if (!empty($data['fecha_inicio'])) {
            $query->where('fecha_inicio', '>=', $data['fecha_inicio']);
        }

        if (!empty($data['fecha_fin'])) {
            $query->where('fecha_fin', '<=', $data['fecha_fin']);
        }

        $this->resultados = $query->orderBy('fecha_inicio', 'desc')->get()->map(function ($avance) {
            $duracion = null;
            $duracionTexto = 'N/A';

            if ($avance->fecha_inicio && $avance->fecha_fin) {
                $inicio = \Carbon\Carbon::parse($avance->fecha_inicio);
                $fin = \Carbon\Carbon::parse($avance->fecha_fin);

                // Calcular la diferencia en horas y minutos
                $totalMinutos = $inicio->diffInMinutes($fin);
                $horas = floor($totalMinutos / 60);
                $minutos = $totalMinutos % 60;

                // Formatear el texto
                if ($horas > 0 && $minutos > 0) {
                    $duracionTexto = "{$horas}h {$minutos}m";
                } elseif ($horas > 0) {
                    $duracionTexto = "{$horas}h";
                } elseif ($minutos > 0) {
                    $duracionTexto = "{$minutos}m";
                } else {
                    $duracionTexto = "0m";
                }

                $duracion = $totalMinutos;
            }

            // Convertir estado a porcentaje
            $porcentaje = match($avance->estado) {
                'pending' => 0,
                'progress' => 50,
                'done' => 100,
                default => 0,
            };

            $estadoLabel = match($avance->estado) {
                'pending' => 'Pendiente',
                'progress' => 'En Progreso',
                'done' => 'Completado',
                default => 'Pendiente',
            };

            // ⏱️ CALCULAR TIEMPOS ENTRE FASES
            // 1. Tiempo de Espera: desde que terminó fase anterior hasta que se liberó esta fase
            $tiempoEspera = null;
            $tiempoEsperaTexto = '-';

            // Buscar la fase anterior del mismo programa que esté finalizada
            $faseAnterior = \App\Models\AvanceFase::where('programa_id', $avance->programa_id)
                ->where('fase_id', '<', $avance->fase_id)
                ->where('estado', 'done')
                ->orderBy('fase_id', 'desc')
                ->first();

            if ($faseAnterior && $faseAnterior->fecha_fin && $avance->fecha_liberacion) {
                $tiempoEspera = \Carbon\Carbon::parse($faseAnterior->fecha_fin)
                    ->diffInMinutes(\Carbon\Carbon::parse($avance->fecha_liberacion));

                $horas = floor($tiempoEspera / 60);
                $minutos = $tiempoEspera % 60;

                if ($horas > 0 && $minutos > 0) {
                    $tiempoEsperaTexto = "{$horas}h {$minutos}m";
                } elseif ($horas > 0) {
                    $tiempoEsperaTexto = "{$horas}h";
                } elseif ($minutos > 0) {
                    $tiempoEsperaTexto = "{$minutos}m";
                } else {
                    $tiempoEsperaTexto = "0m";
                }
            }

            // 2. Tiempo de Reacción: desde que se liberó hasta que se inició
            $tiempoReaccion = null;
            $tiempoReaccionTexto = '-';

            if ($avance->fecha_liberacion && $avance->fecha_inicio) {
                $tiempoReaccion = \Carbon\Carbon::parse($avance->fecha_liberacion)
                    ->diffInMinutes(\Carbon\Carbon::parse($avance->fecha_inicio));

                $horas = floor($tiempoReaccion / 60);
                $minutos = $tiempoReaccion % 60;

                if ($horas > 0 && $minutos > 0) {
                    $tiempoReaccionTexto = "{$horas}h {$minutos}m";
                } elseif ($horas > 0) {
                    $tiempoReaccionTexto = "{$horas}h";
                } elseif ($minutos > 0) {
                    $tiempoReaccionTexto = "{$minutos}m";
                } else {
                    $tiempoReaccionTexto = "0m";
                }
            }

            return [
                'id' => $avance->id,
                'cliente' => $avance->programa->proyecto->cliente->nombre ?? 'N/A',
                'proyecto' => $avance->programa->proyecto->nombre ?? 'N/A',
                'programa' => $avance->programa->nombre ?? 'N/A',
                'fase' => $avance->fase->nombre ?? 'N/A',
                'fecha_liberacion' => $avance->fecha_liberacion ? \Carbon\Carbon::parse($avance->fecha_liberacion)->format('d/m/Y H:i') : '-',
                'fecha_inicio' => $avance->fecha_inicio ? \Carbon\Carbon::parse($avance->fecha_inicio)->format('d/m/Y H:i') : 'N/A',
                'fecha_fin' => $avance->fecha_fin ? \Carbon\Carbon::parse($avance->fecha_fin)->format('d/m/Y H:i') : 'N/A',
                'tiempo_espera' => $tiempoEspera,
                'tiempo_espera_texto' => $tiempoEsperaTexto,
                'tiempo_reaccion' => $tiempoReaccion,
                'tiempo_reaccion_texto' => $tiempoReaccionTexto,
                'duracion' => $duracion,
                'duracion_texto' => $duracionTexto,
                'estado' => $estadoLabel,
                'porcentaje' => $porcentaje,
                'observaciones' => $avance->notas ?? '-',
            ];
        })->toArray();

        if (empty($this->resultados)) {
            Notification::make()
                ->title('No se encontraron resultados')
                ->warning()
                ->send();
        } else {
            Notification::make()
                ->title('Reporte generado exitosamente')
                ->success()
                ->body(count($this->resultados) . ' registros encontrados')
                ->send();
        }
    }

    public function exportarExcel(): mixed
    {
        if (empty($this->resultados)) {
            Notification::make()
                ->title('No hay datos para exportar')
                ->warning()
                ->body('Por favor genera primero el reporte')
                ->send();
            return null;
        }

        return Excel::download(
            new ReporteGeneralExport($this->resultados),
            'reporte_general_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar')
                ->label('Generar Reporte')
                ->icon('heroicon-o-magnifying-glass')
                ->color('primary')
                ->action('generarReporte'),

            Action::make('exportar')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportarExcel')
                ->visible(fn () => !empty($this->resultados)),
        ];
    }

    public function getViewData(): array
    {
        return [
            'resultados' => $this->resultados,
        ];
    }
}
