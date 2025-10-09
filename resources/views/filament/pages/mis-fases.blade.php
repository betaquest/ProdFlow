<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header con instrucciones --}}
        <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-primary-900 dark:text-primary-100 mb-1">
                        Gestiona tu proceso de trabajo
                    </h3>
                    <p class="text-xs text-primary-700 dark:text-primary-300">
                        Cuando creas un programa, automáticamente se te asigna la primera fase. Puedes iniciarla, finalizarla y liberar la siguiente fase para continuar el proceso.
                    </p>
                    <ul class="mt-2 space-y-1 text-xs text-primary-600 dark:text-primary-400">
                        <li class="flex items-center gap-2">
                            <x-heroicon-o-play class="w-4 h-4" />
                            <span><strong>Iniciar:</strong> Marca la fase como "En Progreso"</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            <span><strong>Finalizar:</strong> Marca la fase como completada</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-o-arrow-right-circle class="w-4 h-4" />
                            <span><strong>Liberar Siguiente:</strong> Notifica al responsable de la siguiente fase</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Widgets de estadísticas --}}
        @if ($this->getHeaderWidgets())
            <x-filament-widgets::widgets
                :widgets="$this->getHeaderWidgets()"
                :columns="[
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 4,
                ]"
            />
        @endif

        {{-- Tabla de fases --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
