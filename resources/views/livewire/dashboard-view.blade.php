<div class="h-screen w-screen overflow-hidden flex flex-col text-slate-100">
    {{-- 🧭 ENCABEZADO --}}
    <header class="flex items-center justify-between px-10 py-4 bg-slate-900/90 backdrop-blur-md shadow-lg border-b border-slate-800 relative z-20">
        {{-- IZQUIERDA: LOGO --}}
        <div class="flex items-center gap-4 flex-shrink-0">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-16 w-auto drop-shadow-lg">
        </div>

        {{-- CENTRO: NOMBRE DEL DASHBOARD --}}
        <div class="flex-1 text-center">
            <h1 class="text-5xl font-extrabold tracking-widest uppercase text-slate-50 drop-shadow-lg">
                {{ strtoupper($dashboard->nombre) }}
            </h1>
        </div>

        {{-- DERECHA: HORA Y FECHA --}}
        <div class="text-right flex-shrink-0 leading-tight h-[3rem]" wire:ignore>
            <h2 id="clock" class="text-4xl font-semibold tabular-nums w-[9ch] text-right"></h2>
            <p id="date" class="text-base text-slate-400"></p>
        </div>

    </header>

    {{-- 📊 BARRA DE ESTADÍSTICAS GLOBALES --}}
    <section class="bg-slate-800/80 py-4 border-b border-slate-700 text-2xl font-semibold tracking-wide z-10 shadow-inner">
        <div class="flex justify-center gap-10 mb-3">
            <div class="flex items-center gap-2 text-green-400">
                ✅ {{ $totalDone }}
                <span class="text-slate-300 font-normal">Finalizados</span>
            </div>
            <div class="flex items-center gap-2 text-yellow-300">
                ⏳ {{ $totalProgress }}
                <span class="text-slate-300 font-normal">En Progreso</span>
            </div>
            <div class="flex items-center gap-2 text-slate-400">
                ⬜ {{ $totalPending }}
                <span class="text-slate-300 font-normal">Pendientes</span>
            </div>
            <div class="flex items-center gap-2 text-sky-400">
                🔹 {{ $porcentaje }}%
                <span class="text-slate-300 font-normal">Completado</span>
            </div>
        </div>

        {{-- 🚀 BARRA DE PROGRESO ANIMADA CON TEXTO --}}
        <div class="relative mx-auto w-4/5 h-10 bg-slate-700 rounded-full overflow-hidden shadow-inner">
            {{-- Barra interna animada --}}
            <div
                class="absolute top-0 left-0 h-full rounded-full progress-bar transition-all duration-1000 ease-out"
                style="width: {{ $porcentaje }}%;">
            </div>

            {{-- Texto centrado sobre la barra --}}

            <div class="absolute inset-0 flex items-center justify-center font-bold tracking-widest text-xl drop-shadow-lg"
                style="color: {{ $porcentaje >= 70 ? '#bbf7d0' : ($porcentaje >= 40 ? '#fde68a' : '#f1f5f9') }};">
                {{ $porcentaje }}% COMPLETADO
            </div>
        </div>

    </section>

    {{-- 📊 TABLA PRINCIPAL --}}
    <div class="flex-1 p-6 overflow-x-auto relative z-10">
        <table class="min-w-full border-collapse w-full text-2xl">
            <thead>
                <tr class="bg-slate-800 text-slate-100 border-b border-slate-700">
                    <th class="py-3 px-2 text-left">Cliente</th>
                    <th class="py-3 px-2 text-left">Proyecto</th>
                    <th class="py-3 px-2 text-left">Programa</th>
                    @foreach($fases as $fase)
                        <th class="py-3 px-2">{{ strtoupper($fase->nombre) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody wire:poll.{{ $dashboard->tiempo_actualizacion }}s="loadData">
                @foreach($programas as $programa)
                    <tr class="{{ $loop->even ? 'bg-slate-900/40' : 'bg-slate-900/20' }} hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-2 text-left">{{ $programa->proyecto->cliente->nombre }}</td>
                        <td class="py-3 px-2 text-left">{{ $programa->proyecto->nombre }}</td>
                        <td class="py-3 px-2 text-left font-semibold">{{ $programa->nombre }}</td>

                        @foreach($fases as $fase)
                            @php
                                $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                                $estado = $avance?->estado ?? 'pending';
                                $color = match($estado) {
                                    'done' => 'bg-green-500 text-black',
                                    'progress' => 'bg-yellow-400 text-black animate-pulse',
                                    default => 'bg-slate-600 text-slate-300',
                                };
                                $icon = match($estado) {
                                    'done' => '✅',
                                    'progress' => '⏳',
                                    default => '⬜',
                                };
                            @endphp
                            <td class="text-center py-2 px-2 rounded {{ $color }}">
                                <span class="text-3xl font-bold">{{ $icon }}</span>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ⚙️ PIE --}}
    <footer class="text-center text-slate-400 text-sm py-2 border-t border-slate-800 bg-slate-900/90 backdrop-blur-md z-20">
        Refresca cada {{ $dashboard->tiempo_actualizacion }} segundos — Última actualización: {{ now()->format('H:i:s') }}
    </footer>

    {{-- 🕒 RELOJ EN TIEMPO REAL --}}
    <script>
        let clockInterval;

        function updateClock() {
            const now = new Date();
            const clock = document.getElementById('clock');
            if (!clock) return;
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            clock.textContent = `${hours}:${minutes}:${seconds}`;
        }

        function updateDate() {
            const now = new Date();
            const date = document.getElementById('date');
            if (!date) return;
            date.textContent = now.toLocaleDateString('es-ES', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        }

        function initClock() {
            // Limpiar intervalo anterior si existe
            if (clockInterval) {
                clearInterval(clockInterval);
            }

            updateDate();
            updateClock();
            clockInterval = setInterval(updateClock, 1000);
        }

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', initClock);

        // Reinicializar después de actualización de Livewire
        document.addEventListener('livewire:navigated', initClock);
        document.addEventListener('livewire:load', initClock);
    </script>

    {{-- 🌈 FONDO ANIMADO --}}
    <style>
        body {
            background: linear-gradient(270deg, #0f172a, #1e293b, #334155, #0f172a);
            background-size: 800% 800%;
            animation: gradientShift 25s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        th, td {
            border: none;
        }

        section {
            box-shadow: inset 0 -1px 0 rgba(255,255,255,0.05),
                        0 2px 6px rgba(0,0,0,0.4);
        }
    </style>
</div>
