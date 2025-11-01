<div class="h-screen w-screen overflow-hidden flex flex-col text-slate-100">
    {{-- 🧭 ENCABEZADO --}}
    <header class="flex items-center justify-between px-10 py-4 bg-slate-900/90 backdrop-blur-md shadow-lg border-b border-slate-800 relative z-20">
        {{-- IZQUIERDA: LOGO (condicional) --}}
        @if($dashboard->mostrar_logotipo)
            <div class="flex items-center gap-4 flex-shrink-0">
                <img src="{{ asset('logo_h.png') }}" alt="Logo" class="h-16 w-auto drop-shadow-lg">
            </div>
        @else
            <div class="flex-shrink-0 w-16"></div>
        @endif

        {{-- CENTRO: NOMBRE DEL DASHBOARD (condicional) --}}
        @if($dashboard->mostrar_titulo)
            <div class="flex-1 text-center">
                <h1 class="text-5xl font-extrabold tracking-widest uppercase text-slate-50 drop-shadow-lg">
                    {{ strtoupper($dashboard->nombre) }}
                </h1>
            </div>
        @else
            <div class="flex-1"></div>
        @endif

        {{-- DERECHA: HORA Y FECHA (condicional) --}}
        @if($dashboard->mostrar_reloj)
            <div class="text-right flex-shrink-0 leading-tight h-[3rem]" wire:ignore>
                <h2 id="clock" class="text-4xl font-semibold tabular-nums w-[9ch] text-right"></h2>
                <p id="date" class="text-base text-slate-400"></p>
            </div>
        @else
            <div class="flex-shrink-0 w-16"></div>
        @endif

    </header>

    {{-- 📊 BARRA DE ESTADÍSTICAS GLOBALES --}}
    @if($dashboard->mostrar_estadisticas)
        <section class="bg-slate-800/80 py-4 border-b border-slate-700 text-2xl font-semibold tracking-wide z-10 shadow-inner">
            <div class="flex justify-center gap-10 {{ $dashboard->mostrar_barra_progreso ? 'mb-3' : '' }}">
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
            @if($dashboard->mostrar_barra_progreso)
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
            @endif

        </section>
    @endif

    {{-- 📊 TABLA PRINCIPAL --}}
    <div id="tabla-container" class="flex-1 p-6 overflow-x-auto relative z-10">
        <table class="min-w-full border-collapse w-full text-2xl">
            <thead>
                <tr class="bg-slate-800 text-slate-100 border-b border-slate-700">
                    <th class="py-3 px-2 text-left">Cliente</th>
                    <th class="py-3 px-2 text-left">Proyecto</th>
                    <th class="py-3 px-2 text-left">Programa</th>
                    @foreach($fases as $fase)
                        <th class="py-3 px-2">
                            {{ strtoupper($dashboard->usar_alias_fases && $fase->alias ? $fase->alias : $fase->nombre) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody wire:poll.{{ $dashboard->tiempo_actualizacion }}s="loadData" id="tabla-body">
                @foreach($programas as $index => $programa)
                    @php
                        $tieneAlerta = in_array($programa->id, $programasConAlerta);
                        $estaFinalizado = in_array($programa->id, $programasFinalizados);

                        if ($tieneAlerta) {
                            $clasesFila = 'bg-red-600/40 border-l-8 border-red-500 shadow-lg shadow-red-900/50 hover:bg-red-600/50 animate-pulse-slow';
                        } elseif ($estaFinalizado) {
                            $clasesFila = 'bg-green-600/30 border-l-8 border-green-500 shadow-lg shadow-green-900/50 hover:bg-green-600/40';
                        } else {
                            $clasesFila = ($loop->even ? 'bg-slate-900/40' : 'bg-slate-900/20') . ' hover:bg-slate-800/40';
                        }
                    @endphp
                    <tr class="{{ $clasesFila }} transition-all duration-300" data-programa-index="{{ $index }}">
                        <td class="py-3 px-2 text-left {{ $tieneAlerta ? 'font-semibold' : '' }} {{ $estaFinalizado ? 'font-semibold text-green-200' : '' }}">
                            {{ $programa->proyecto->cliente->nombre }}
                        </td>
                        <td class="py-3 px-2 text-left {{ $tieneAlerta ? 'font-semibold' : '' }} {{ $estaFinalizado ? 'font-semibold text-green-200' : '' }}">
                            {{ $programa->proyecto->nombre }}
                        </td>
                        <td class="py-3 px-2 text-left">
                            <div class="font-semibold {{ $tieneAlerta ? 'text-red-200' : '' }} {{ $estaFinalizado ? 'text-green-200' : '' }}">
                                {{ $programa->nombre }}
                            </div>
                            @if($programa->descripcion)
                                <div class="text-sm {{ $tieneAlerta ? 'text-red-300' : ($estaFinalizado ? 'text-green-300' : 'text-slate-400') }} mt-1">
                                    {{ \Illuminate\Support\Str::limit($programa->descripcion, 80) }}
                                </div>
                            @endif
                        </td>

                        @php
                            // Obtener IDs de fases configuradas para este programa
                            $fasesProgramaIds = $programa->getFasesConfiguradasIds();
                        @endphp
                        @foreach($fases as $fase)
                            @php
                                // Solo mostrar si la fase está configurada para este programa
                                if (!in_array($fase->id, $fasesProgramaIds)) {
                                    echo '<td class="py-2 px-2 rounded bg-slate-700/30"><span class="text-slate-500 text-xl">—</span></td>';
                                    continue;
                                }

                                $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                                $estado = $avance?->estado ?? 'pending';

                                // Inicializar variables
                                $mostrarLiberar = false;
                                $mostrarPendiente = false;

                                // Verificar si existe la siguiente fase DENTRO de las configuradas para este programa
                                $fasesDelPrograma = $fases->whereIn('id', $fasesProgramaIds);
                                $siguienteFase = $fasesDelPrograma->where('orden', '>', $fase->orden)->first();

                                // Determinar color y estilo basado en estado
                                if ($estado === 'done') {
                                    // Si no hay siguiente fase configurada, mostrar como liberada
                                    if (!$siguienteFase) {
                                        // Última fase completada - verde brillante
                                        $color = 'bg-green-500 text-black border-2 border-green-300 shadow-lg shadow-green-500/50';
                                        $icon = '✅';
                                    } else {
                                        // Hay siguiente fase, verificar si ya tiene avance
                                        $avanceSiguiente = $programa->avances->firstWhere('fase_id', $siguienteFase->id);

                                        if ($avanceSiguiente) {
                                            // Ya fue liberada (existe siguiente fase)
                                            $color = 'bg-green-500 text-black border-2 border-green-300 shadow-lg shadow-green-500/50';
                                            $icon = '✅';
                                        } else {
                                            // Completada pero pendiente de liberación
                                            $color = 'bg-green-700 text-white border-2 border-yellow-400 animate-pulse';
                                            $icon = '⏸️';
                                            $mostrarLiberar = true;
                                        }
                                    }
                                } elseif ($estado === 'progress') {
                                    $color = 'bg-yellow-400 text-black animate-pulse';
                                    $icon = '⏳';
                                } else {
                                    // Estado 'pending' - necesitamos distinguir si fue liberada o no
                                    if (!$avance) {
                                        // No existe el avance - fase aún no liberada
                                        $color = 'bg-slate-600 text-slate-300';
                                        $icon = '⬜';
                                    } else {
                                        // Existe el avance pero está en pending - fase liberada lista para iniciar
                                        $color = 'bg-blue-500 text-white border-2 border-blue-300 shadow-lg shadow-blue-500/50';
                                        $icon = '🔔';
                                        $mostrarPendiente = true;
                                    }
                                }
                            @endphp
                            <td class="text-center py-2 px-2 rounded {{ $color }}">
                                <span class="text-3xl font-bold">{{ $icon }}</span>
                                @if($mostrarLiberar)
                                    <div class="text-xs mt-1 font-semibold">LIBERAR</div>
                                @endif
                                @if($mostrarPendiente)
                                    <div class="text-xs mt-1 font-semibold">PENDIENTE</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ⚙️ PIE --}}
    @if(!$dashboard->ocultar_footer)
        <footer class="text-center text-slate-400 text-sm border-t border-slate-800 bg-slate-900/90 backdrop-blur-md z-20 relative overflow-hidden">
            {{-- Texto del footer --}}
            <div class="relative z-10 py-2">
                Refresca cada {{ $dashboard->tiempo_actualizacion }} segundos — Última actualización: {{ now()->format('H:i:s') }}
            </div>

            {{-- Barra de progreso sutil en la parte inferior --}}
            <div class="absolute bottom-0 left-0 w-full h-1.5 bg-slate-700/50">
                <div id="progress-bar" class="h-full bg-gradient-to-r from-blue-500 to-blue-400" style="width: 0%; transition: width 0.5s linear; box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);"></div>
            </div>
        </footer>

        {{-- Script para animar la barra de progreso --}}
        <script>
            (function() {
                const progressBar = document.getElementById('progress-bar');
                if (!progressBar) return;

                const updateInterval = {{ $dashboard->tiempo_actualizacion }} * 1000; // Convertir a milisegundos
                let startTime = Date.now();

                function updateProgress() {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min((elapsed / updateInterval) * 100, 100);
                    progressBar.style.width = progress + '%';

                    if (progress < 100) {
                        requestAnimationFrame(updateProgress);
                    }
                }

                function resetProgress() {
                    startTime = Date.now();
                    progressBar.style.width = '0%';
                    requestAnimationFrame(updateProgress);
                }

                // Iniciar la animación
                resetProgress();

                // Reiniciar cuando Livewire actualiza los datos
                Livewire.hook('message.processed', (message, component) => {
                    resetProgress();
                });
            })();
        </script>
    @endif

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

    @if($dashboard->modo_visualizacion === 'scroll')
    {{-- 🔄 AUTO-SCROLL ELEGANTE --}}
    <script>
        (function() {
            const container = document.getElementById('tabla-container');
            if (!container) return;

            const velocidad = {{ $dashboard->auto_scroll_velocidad ?? 30 }}; // segundos
            const pausa = {{ $dashboard->auto_scroll_pausa ?? 3 }}; // segundos

            let animationId = null;
            let scrollTimeout = null;
            let isScrolling = false;
            let userInteracted = false;
            let pauseTimeout = null;

            // Función para verificar si hay overflow (más contenido del visible)
            function hasOverflow() {
                return container.scrollHeight > container.clientHeight;
            }

            // Función de animación suave
            function smoothScroll() {
                if (!hasOverflow() || userInteracted) {
                    return;
                }

                const maxScroll = container.scrollHeight - container.clientHeight;
                const currentScroll = container.scrollTop;
                const isAtTop = currentScroll <= 0;
                const isAtBottom = currentScroll >= maxScroll - 5;

                // Si está en un extremo, hacer pausa
                if (isAtTop || isAtBottom) {
                    if (!isScrolling) {
                        isScrolling = true;

                        // Pausa antes de continuar
                        pauseTimeout = setTimeout(() => {
                            isScrolling = false;
                            startAutoScroll();
                        }, pausa * 1000);

                        return;
                    }
                }

                // Calcular dirección (si está arriba va abajo, si está abajo va arriba)
                const targetScroll = currentScroll < maxScroll / 2 ? maxScroll : 0;
                const distance = Math.abs(targetScroll - currentScroll);
                const duration = velocidad * 1000; // convertir a milisegundos
                const startTime = performance.now();

                function animate(currentTime) {
                    if (userInteracted) return;

                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);

                    // Easing function (ease-in-out)
                    const easeProgress = progress < 0.5
                        ? 2 * progress * progress
                        : 1 - Math.pow(-2 * progress + 2, 2) / 2;

                    const newScroll = currentScroll + (targetScroll - currentScroll) * easeProgress;
                    container.scrollTop = newScroll;

                    if (progress < 1) {
                        animationId = requestAnimationFrame(animate);
                    } else {
                        // Al completar, esperar pausa y continuar
                        pauseTimeout = setTimeout(() => {
                            smoothScroll();
                        }, pausa * 1000);
                    }
                }

                animationId = requestAnimationFrame(animate);
            }

            function startAutoScroll() {
                if (!hasOverflow()) {
                    console.log('No hay suficiente contenido para hacer scroll automático');
                    return;
                }

                // Pequeño delay inicial
                scrollTimeout = setTimeout(() => {
                    smoothScroll();
                }, 2000);
            }

            // Detener auto-scroll cuando el usuario interactúa
            function stopAutoScroll() {
                userInteracted = true;
                if (animationId) {
                    cancelAnimationFrame(animationId);
                    animationId = null;
                }
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = null;
                }
                if (pauseTimeout) {
                    clearTimeout(pauseTimeout);
                    pauseTimeout = null;
                }
            }

            // Eventos para detectar interacción del usuario
            container.addEventListener('wheel', stopAutoScroll, { passive: true });
            container.addEventListener('touchstart', stopAutoScroll, { passive: true });
            container.addEventListener('mousedown', stopAutoScroll);

            // Reanudar después de 10 segundos sin interacción
            let resumeTimeout = null;
            function scheduleResume() {
                if (resumeTimeout) clearTimeout(resumeTimeout);
                resumeTimeout = setTimeout(() => {
                    userInteracted = false;
                    startAutoScroll();
                }, 10000);
            }

            container.addEventListener('wheel', scheduleResume, { passive: true });
            container.addEventListener('touchend', scheduleResume, { passive: true });
            container.addEventListener('mouseup', scheduleResume);

            // Iniciar cuando se carga la página
            function initAutoScroll() {
                userInteracted = false;
                stopAutoScroll(); // Limpiar cualquier animación previa
                startAutoScroll();
            }

            // Iniciar al cargar
            document.addEventListener('DOMContentLoaded', initAutoScroll);
            document.addEventListener('livewire:navigated', initAutoScroll);
            document.addEventListener('livewire:load', initAutoScroll);

            // Reiniciar después de actualización de Livewire
            Livewire.hook('message.processed', (message, component) => {
                // Esperar a que el DOM se actualice
                setTimeout(() => {
                    if (!userInteracted && hasOverflow()) {
                        startAutoScroll();
                    }
                }, 500);
            });
        })();
    </script>
    @endif

    @if($dashboard->modo_visualizacion === 'paginacion')
    {{-- 📄 PAGINACIÓN AUTOMÁTICA --}}
    <script>
        (function() {
            const tbody = document.getElementById('tabla-body');
            if (!tbody) return;

            const porPagina = {{ $dashboard->paginacion_cantidad ?? 5 }};
            const tiempoPorPagina = {{ $dashboard->paginacion_tiempo ?? 10 }} * 1000; // Convertir a milisegundos

            let paginaActual = 0;
            let totalProgramas = 0;
            let totalPaginas = 0;
            let intervalId = null;

            function contarProgramas() {
                const filas = tbody.querySelectorAll('tr[data-programa-index]');
                totalProgramas = filas.length;
                totalPaginas = Math.ceil(totalProgramas / porPagina);
            }

            function mostrarPagina(numeroPagina) {
                const filas = tbody.querySelectorAll('tr[data-programa-index]');
                const inicio = numeroPagina * porPagina;
                const fin = inicio + porPagina;

                filas.forEach((fila, index) => {
                    if (index >= inicio && index < fin) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            }

            function siguientePagina() {
                paginaActual = (paginaActual + 1) % totalPaginas;
                mostrarPagina(paginaActual);
            }

            function iniciarPaginacion() {
                contarProgramas();

                if (totalPaginas <= 1) {
                    // Si solo hay una página o menos, mostrar todo
                    mostrarPagina(0);
                    if (intervalId) {
                        clearInterval(intervalId);
                        intervalId = null;
                    }
                    return;
                }

                // Mostrar la primera página
                paginaActual = 0;
                mostrarPagina(paginaActual);

                // Configurar el cambio automático
                if (intervalId) {
                    clearInterval(intervalId);
                }
                intervalId = setInterval(siguientePagina, tiempoPorPagina);
            }

            // Iniciar al cargar la página
            document.addEventListener('DOMContentLoaded', iniciarPaginacion);
            document.addEventListener('livewire:navigated', iniciarPaginacion);
            document.addEventListener('livewire:load', iniciarPaginacion);

            // Reiniciar después de actualización de Livewire
            Livewire.hook('message.processed', (message, component) => {
                setTimeout(iniciarPaginacion, 500);
            });
        })();
    </script>
    @endif

    {{-- 🌈 FONDO ANIMADO --}}
    <style>
        body {
            @if($dashboard->color_fondo)
                background-color: {{ $dashboard->color_fondo }};
            @else
                background: linear-gradient(270deg, #0f172a, #1e293b, #334155, #0f172a);
                background-size: 800% 800%;
                animation: gradientShift 25s ease infinite;
            @endif
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes pulse-slow {
            0%, 100% {
                background-color: rgba(220, 38, 38, 0.4);
                box-shadow: 0 10px 15px -3px rgba(127, 29, 29, 0.5);
            }
            50% {
                background-color: rgba(220, 38, 38, 0.5);
                box-shadow: 0 10px 15px -3px rgba(127, 29, 29, 0.7);
            }
        }

        .animate-pulse-slow {
            animation: pulse-slow 3s ease-in-out infinite;
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
