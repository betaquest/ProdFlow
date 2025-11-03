<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Información del Programa --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Programa</h3>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $record->nombre }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Proyecto</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $record->proyecto->nombre }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->proyecto->cliente->nombre }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Progreso</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-white">
                        @php
                            $fasesConfiguradas = $record->getFasesConfiguradas();
                            $totalFases = $fasesConfiguradas->count();
                            $fasesCompletadas = 0;
                            foreach ($fasesConfiguradas as $fase) {
                                $avance = $record->avances->firstWhere('fase_id', $fase->id);
                                if ($avance && $avance->estado === 'done') {
                                    $fasesCompletadas++;
                                }
                            }
                            $porcentaje = $totalFases > 0 ? round(($fasesCompletadas / $totalFases) * 100) : 0;
                        @endphp
                        {{ $fasesCompletadas }} / {{ $totalFases }} fases ({{ $porcentaje }}%)
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tiempo Total</h3>
                    <p class="mt-1 text-lg text-gray-900 dark:text-white">
                        @php
                            $avances = $this->getAvances();
                            $fechaInicio = null;
                            $fechaFin = null;

                            // Buscar la fecha de inicio más temprana
                            foreach ($avances as $avance) {
                                if ($avance->fecha_inicio) {
                                    if (!$fechaInicio || $avance->fecha_inicio < $fechaInicio) {
                                        $fechaInicio = $avance->fecha_inicio;
                                    }
                                }
                            }

                            // Buscar la fecha de finalización más reciente de las fases completadas
                            foreach ($avances as $avance) {
                                if ($avance->fecha_fin && $avance->estado === 'done') {
                                    if (!$fechaFin || $avance->fecha_fin > $fechaFin) {
                                        $fechaFin = $avance->fecha_fin;
                                    }
                                }
                            }

                            // Calcular tiempo total
                            $tiempoTotal = '—';
                            if ($fechaInicio && $fechaFin) {
                                $tiempoTotal = $fechaInicio->diffForHumans($fechaFin, true);
                            } elseif ($fechaInicio) {
                                $tiempoTotal = 'En progreso (' . $fechaInicio->diffForHumans(now(), true) . ')';
                            }
                        @endphp
                        {{ $tiempoTotal }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Timeline de Fases</h2>

            <div class="relative">
                {{-- Línea vertical del timeline --}}
                <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                <div class="space-y-8">
                    @foreach($this->getAvances() as $avance)
                        <div class="relative flex items-start gap-6">
                            {{-- Punto del timeline --}}
                            <div class="relative z-10 flex-shrink-0">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full border-4
                                    @if($avance->estado === 'done')
                                        bg-green-500 border-green-600 dark:bg-green-600 dark:border-green-700
                                    @elseif($avance->estado === 'progress')
                                        bg-yellow-500 border-yellow-600 dark:bg-yellow-600 dark:border-yellow-700 animate-pulse
                                    @else
                                        bg-gray-300 border-gray-400 dark:bg-gray-600 dark:border-gray-700
                                    @endif
                                ">
                                    @if($avance->estado === 'done')
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @elseif($avance->estado === 'progress')
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            {{-- Contenido del timeline --}}
                            <div class="flex-1 min-w-0">
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 border-2
                                    @if($avance->estado === 'done')
                                        border-green-200 dark:border-green-800
                                    @elseif($avance->estado === 'progress')
                                        border-yellow-200 dark:border-yellow-800
                                    @else
                                        border-gray-200 dark:border-gray-700
                                    @endif
                                ">
                                    {{-- Cabecera de la fase --}}
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ $avance->fase->nombre }}
                                            </h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($avance->estado === 'done')
                                                        bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                    @elseif($avance->estado === 'progress')
                                                        bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                    @else
                                                        bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300
                                                    @endif
                                                ">
                                                    @if($avance->estado === 'done')
                                                        ✓ Completada
                                                    @elseif($avance->estado === 'progress')
                                                        ⏳ En Progreso
                                                    @else
                                                        ⏸ Pendiente
                                                    @endif
                                                </span>

                                                @if($avance->area)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                        📁 {{ $avance->area->nombre }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Detalles de fechas y responsable --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        @if($avance->fecha_inicio)
                                            <div>
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Iniciada:</span>
                                                <p class="text-gray-600 dark:text-gray-400">{{ $avance->fecha_inicio->format('d/m/Y H:i') }}</p>
                                            </div>
                                        @endif

                                        @if($avance->fecha_fin)
                                            <div>
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Finalizada:</span>
                                                <p class="text-gray-600 dark:text-gray-400">{{ $avance->fecha_fin->format('d/m/Y H:i') }}</p>
                                            </div>
                                        @endif

                                        @if($avance->fecha_liberacion)
                                            <div>
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Liberada:</span>
                                                <p class="text-gray-600 dark:text-gray-400">{{ $avance->fecha_liberacion->format('d/m/Y H:i') }}</p>
                                            </div>
                                        @endif

                                        @if($avance->responsable)
                                            <div>
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Responsable:</span>
                                                <p class="text-gray-600 dark:text-gray-400">👤 {{ $avance->responsable->name }}</p>
                                            </div>
                                        @endif

                                        @if($avance->fecha_inicio && $avance->fecha_fin)
                                            <div>
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Duración:</span>
                                                <p class="text-gray-600 dark:text-gray-400">
                                                    {{ $avance->fecha_inicio->diffForHumans($avance->fecha_fin, true) }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Notas de inicio --}}
                                    @if($avance->notas)
                                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                                            <span class="font-medium text-blue-900 dark:text-blue-300 text-sm">📝 Notas de inicio:</span>
                                            <p class="text-blue-800 dark:text-blue-400 text-sm mt-1">{{ $avance->notas }}</p>
                                        </div>
                                    @endif

                                    {{-- Notas de finalización --}}
                                    @if($avance->notas_finalizacion)
                                        <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-800">
                                            <span class="font-medium text-green-900 dark:text-green-300 text-sm">✓ Notas de finalización:</span>
                                            <p class="text-green-800 dark:text-green-400 text-sm mt-1">{{ $avance->notas_finalizacion }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($this->getAvances()->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay fases registradas</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Este programa aún no tiene avances de fases.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
