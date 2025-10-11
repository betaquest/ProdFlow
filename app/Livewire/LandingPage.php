<?php

namespace App\Livewire;

use App\Models\Dashboard;
use Livewire\Component;

class LandingPage extends Component
{
    public function render()
    {
        $dashboards = Dashboard::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('livewire.landing-page', [
            'dashboards' => $dashboards,
        ]);
    }
}
