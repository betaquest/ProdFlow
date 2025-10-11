<?php

namespace App\Livewire;

use App\Models\Dashboard;
use App\Models\Fase;
use App\Models\Programa;
use Livewire\Component;

class DashboardView extends Component
{
    public Dashboard $dashboard;

    public $programas;

    public $fases;

    // Estadísticas globales
    public int $totalDone = 0;

    public int $totalProgress = 0;

    public int $totalPending = 0;

    public float $porcentaje = 0.0;

    public function mount(Dashboard $dashboard)
    {
        abort_unless($dashboard->activo, 404);
        $this->dashboard = $dashboard;
        $this->fases = Fase::orderBy('orden', 'asc')->get();
        $this->loadData();
    }

    public function loadData()
    {
        $query = Programa::query()->with(['proyecto.cliente', 'avances.fase']);

        // Filtrar por clientes si no se muestran todos
        if (!$this->dashboard->todos_clientes && $this->dashboard->clientes_ids) {
            $query->whereHas('proyecto.cliente', function ($q) {
                $q->whereIn('clientes.id', $this->dashboard->clientes_ids);
            });
        }

        // Aplicar criterios adicionales (JSON)
        if ($this->dashboard->criterios) {
            foreach ($this->dashboard->criterios as $campo => $valor) {
                $query->where($campo, $valor);
            }
        }

        $this->programas = $query->get();

        // Recalcular estadísticas
        $this->totalDone = 0;
        $this->totalProgress = 0;
        $this->totalPending = 0;
        $totalFases = 0;

        foreach ($this->programas as $programa) {
            foreach ($this->fases as $fase) {
                $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                $estado = $avance?->estado ?? 'pending';
                $totalFases++;

                match ($estado) {
                    'done' => $this->totalDone++,
                    'progress' => $this->totalProgress++,
                    default => $this->totalPending++,
                };
            }
        }

        $this->porcentaje = $totalFases > 0 ? round(($this->totalDone / $totalFases) * 100, 1) : 0;
    }

    public function render()
    {
        return view('livewire.dashboard-view')
            ->layout('components.layouts.dashboard');
    }
}
