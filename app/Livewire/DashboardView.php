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

    // Programas con alerta de antigüedad
    public array $programasConAlerta = [];

    // Programas completamente finalizados
    public array $programasFinalizados = [];

    public function mount(Dashboard $dashboard)
    {
        abort_unless($dashboard->activo, 404);
        $this->dashboard = $dashboard;

        // Cargar fases según configuración
        if (!$this->dashboard->todas_fases && $this->dashboard->fases_ids) {
            // Solo las fases seleccionadas
            $this->fases = Fase::whereIn('id', $this->dashboard->fases_ids)
                ->orderBy('orden', 'asc')
                ->get();
        } else {
            // Todas las fases
            $this->fases = Fase::orderBy('orden', 'asc')->get();
        }

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

        // Filtrar solo programas en proceso
        if ($this->dashboard->mostrar_solo_en_proceso) {
            $query->whereHas('avances', function ($q) {
                $q->where('estado', 'progress');
            });
        }

        // Aplicar ordenamiento
        $ordenamiento = $this->dashboard->orden_programas ?? 'nombre';
        switch ($ordenamiento) {
            case 'cliente':
                $query->join('proyectos', 'programas.proyecto_id', '=', 'proyectos.id')
                      ->join('clientes', 'proyectos.cliente_id', '=', 'clientes.id')
                      ->orderBy('clientes.nombre', 'asc')
                      ->select('programas.*');
                break;
            case 'proyecto':
                $query->join('proyectos', 'programas.proyecto_id', '=', 'proyectos.id')
                      ->orderBy('proyectos.nombre', 'asc')
                      ->select('programas.*');
                break;
            case 'nombre':
            default:
                $query->orderBy('nombre', 'asc');
                break;
        }

        $programas = $query->get();

        // Filtrar programas finalizados antiguos si está habilitado
        if ($this->dashboard->ocultar_finalizados_antiguos) {
            $hoy = now()->startOfDay();

            $programas = $programas->filter(function ($programa) use ($hoy) {
                $todasFasesCompletadas = true;
                $ultimaFechaFinalizacion = null;

                foreach ($this->fases as $fase) {
                    $avance = $programa->avances->firstWhere('fase_id', $fase->id);

                    // Si la fase no existe o no está completada, el programa sigue activo
                    if (!$avance || $avance->estado !== 'done') {
                        $todasFasesCompletadas = false;
                        break;
                    }

                    // Registrar la última fecha de finalización
                    if ($avance->fecha_fin) {
                        $ultimaFechaFinalizacion = max($ultimaFechaFinalizacion, $avance->fecha_fin);
                    }
                }

                // Si no todas las fases están completadas, siempre mostrarlo
                if (!$todasFasesCompletadas) {
                    return true;
                }

                // Si todas las fases están completadas, solo mostrarlo si la última finalización fue hoy
                if ($ultimaFechaFinalizacion) {
                    return $ultimaFechaFinalizacion->isSameDay($hoy);
                }

                // Si no tiene fecha de finalización pero está completado, mostrarlo
                return true;
            });
        }

        // Detectar programas completamente finalizados y filtrar si es necesario
        $this->programasFinalizados = [];
        $programasFiltrados = collect();

        foreach ($programas as $programa) {
            // Obtener solo las fases configuradas para este programa
            $fasesPrograma = $programa->getFasesConfiguradasIds();
            $fasesProgramaObjs = $this->fases->whereIn('id', $fasesPrograma);

            $todasFasesCompletadas = true;
            foreach ($fasesProgramaObjs as $fase) {
                $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                if (!$avance || $avance->estado !== 'done') {
                    $todasFasesCompletadas = false;
                    break;
                }
            }

            // Marcar programa como finalizado
            if ($todasFasesCompletadas && $fasesProgramaObjs->isNotEmpty()) {
                $this->programasFinalizados[] = $programa->id;
            }

            // Filtrar programas completamente finalizados si está activado
            if ($this->dashboard->ocultar_completamente_finalizados && $todasFasesCompletadas && $fasesProgramaObjs->isNotEmpty()) {
                continue; // No agregar este programa
            }

            $programasFiltrados->push($programa);
        }

        $this->programas = $programasFiltrados;

        // Calcular alertas de antigüedad (solo para programas NO finalizados)
        $this->programasConAlerta = [];
        if ($this->dashboard->alerta_antiguedad_activa && $this->dashboard->alerta_antiguedad_dias > 0) {
            $fechaLimite = now()->subDays($this->dashboard->alerta_antiguedad_dias);

            foreach ($this->programas as $programa) {
                // No alertar programas finalizados
                if (in_array($programa->id, $this->programasFinalizados)) {
                    continue;
                }

                // Si no está completado y es más antiguo que el límite, agregar a alertas
                if ($programa->created_at < $fechaLimite) {
                    $this->programasConAlerta[] = $programa->id;
                }
            }
        }

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
