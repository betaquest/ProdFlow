<?php

namespace App\Filament\Pages;

use App\Models\Programa;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

class EstadoProgramas extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.estado-programas';

    protected static ?string $title = 'Estado de Programas';

    protected static ?string $navigationLabel = 'Estado de Programas';

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 101;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()->can('programas.ver_reportes');
    }

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'cliente_id' => null,
            'estado' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('fecha_inicio')
                    ->label('Fecha Inicio (desde)')
                    ->placeholder('Selecciona una fecha'),
                DatePicker::make('fecha_fin')
                    ->label('Fecha Fin (hasta)')
                    ->placeholder('Selecciona una fecha'),
                Select::make('cliente_id')
                    ->label('Cliente')
                    ->options(\App\Models\Cliente::pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Todos los clientes'),
                Select::make('estado')
                    ->label('Estado del Programa')
                    ->options([
                        'sin_iniciar' => '⬜ Sin Iniciar',
                        'en_progreso' => '⏳ En Progreso',
                        'pausado' => '⏸️ Pausado',
                        'completado' => '✅ Completado',
                    ])
                    ->placeholder('Todos los estados'),
            ])
            ->columns(4)
            ->statePath('data');
    }

    public function getProgramasData(): array
    {
        $query = Programa::with([
            'proyecto.cliente',
            'avances.fase',
            'perfilPrograma'
        ])->where('activo', true);

        // Aplicar filtros
        if (!empty($this->data['cliente_id'])) {
            $query->whereHas('proyecto', function ($q) {
                $q->where('cliente_id', $this->data['cliente_id']);
            });
        }

        if (!empty($this->data['fecha_inicio'])) {
            $query->whereHas('avances', function ($q) {
                $q->where('fecha_inicio', '>=', $this->data['fecha_inicio']);
            });
        }

        if (!empty($this->data['fecha_fin'])) {
            $query->whereHas('avances', function ($q) {
                $q->where('fecha_fin', '<=', $this->data['fecha_fin']);
            });
        }

        $programas = $query->get();

        // Filtrar por estado si está seleccionado
        if (!empty($this->data['estado'])) {
            $programas = $programas->filter(function ($programa) {
                return $this->determinarEstadoPrograma($programa) === $this->data['estado'];
            });
        }

        return $programas->map(function ($programa) {
            $fasesConfiguradas = $programa->getFasesConfiguradas();
            $totalFases = $fasesConfiguradas->count();
            $fasesCompletadas = 0;
            $faseActual = null;
            $fechaInicio = null;
            $fechaFin = null;

            foreach ($fasesConfiguradas as $fase) {
                $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                if ($avance) {
                    if ($avance->estado === 'done') {
                        $fasesCompletadas++;
                    } elseif ($avance->estado === 'progress') {
                        $faseActual = $fase->nombre;
                    }

                    // Obtener fechas
                    if (!$fechaInicio || ($avance->fecha_inicio && $avance->fecha_inicio < $fechaInicio)) {
                        $fechaInicio = $avance->fecha_inicio;
                    }
                    if ($avance->fecha_fin && (!$fechaFin || $avance->fecha_fin > $fechaFin)) {
                        $fechaFin = $avance->fecha_fin;
                    }
                }
            }

            // Si no hay fase en progreso, buscar la siguiente pendiente
            if (!$faseActual) {
                foreach ($fasesConfiguradas as $fase) {
                    $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                    if (!$avance || $avance->estado === 'pending') {
                        $faseActual = $fase->nombre . ' (Pendiente)';
                        break;
                    }
                }
            }

            $porcentaje = $totalFases > 0 ? round(($fasesCompletadas / $totalFases) * 100, 1) : 0;
            $estado = $this->determinarEstadoPrograma($programa);

            return [
                'cliente' => $programa->proyecto->cliente->nombre ?? 'N/A',
                'proyecto' => $programa->proyecto->nombre ?? 'N/A',
                'programa' => $programa->nombre,
                'fecha_inicio' => $fechaInicio?->format('d/m/Y') ?? '—',
                'fecha_fin' => $fechaFin?->format('d/m/Y') ?? '—',
                'fase_actual' => $faseActual ?? 'Sin iniciar',
                'porcentaje' => $porcentaje,
                'estado' => $estado,
                'fases_completadas' => $fasesCompletadas,
                'total_fases' => $totalFases,
            ];
        })->toArray();
    }

    protected function determinarEstadoPrograma(Programa $programa): string
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

    public function aplicarFiltros(): void
    {
        // Este método fuerza la recarga de la vista con los filtros aplicados
        $this->render();
    }
}
