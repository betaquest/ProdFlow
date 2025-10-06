<?php

namespace App\Http\Controllers;

class DashboardViewController extends Controller
{
    public function show(Dashboard $dashboard)
    {
        // Si el dashboard está inactivo, mostramos 404
        abort_unless($dashboard->activo, 404);

        // Aquí aplicamos los criterios de filtro personalizados
        $query = Programa::query()->with(['proyecto.cliente', 'avances.fase']);

        if ($dashboard->criterios) {
            foreach ($dashboard->criterios as $campo => $valor) {
                $query->where($campo, $valor);
            }
        }

        $programas = $query->get();

        return view('dashboards.show', compact('dashboard', 'programas'));
    }
}
