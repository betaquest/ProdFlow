<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filtros --}}
        <x-filament::section>
            <x-slot name="heading">
                Filtros
            </x-slot>

            <form wire:submit="aplicarFiltros">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit" icon="heroicon-o-funnel">
                        Aplicar Filtros
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="gray"
                        icon="heroicon-o-x-mark"
                        wire:click="$set('data', [])"
                    >
                        Limpiar Filtros
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Tabla de Programas --}}
        <x-filament::section>
            <x-slot name="heading">
                Reporte Detallado de Programas
            </x-slot>

            <x-slot name="headerEnd">
                <x-filament::button
                    icon="heroicon-o-arrow-down-tray"
                    color="success"
                    tag="a"
                    href="#"
                    onclick="exportTableToCSV('reporte-programas.csv'); return false;"
                >
                    Exportar a CSV
                </x-filament::button>
            </x-slot>

            @php
                $programas = $this->getProgramasData();
            @endphp

            <div class="overflow-x-auto">
                <table id="tabla-programas" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Proyecto
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Programa
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Fecha Inicio
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Fecha Fin
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Fase Actual
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Progreso
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Porcentaje
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($programas as $programa)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $programa['cliente'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $programa['proyecto'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $programa['programa'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $programa['fecha_inicio'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $programa['fecha_fin'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $programa['fase_actual'] }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($programa['estado'] === 'completado')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            ✅ Completado
                                        </span>
                                    @elseif($programa['estado'] === 'en_progreso')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            ⏳ En Progreso
                                        </span>
                                    @elseif($programa['estado'] === 'pausado')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            ⏸️ Pausado
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            ⬜ Sin Iniciar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $programa['fases_completadas'] }}/{{ $programa['total_fases'] }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                            <div
                                                class="h-full rounded-full transition-all duration-300
                                                    @if($programa['porcentaje'] == 100) bg-green-500
                                                    @elseif($programa['porcentaje'] >= 75) bg-blue-500
                                                    @elseif($programa['porcentaje'] >= 50) bg-yellow-500
                                                    @elseif($programa['porcentaje'] >= 25) bg-orange-500
                                                    @else bg-red-500
                                                    @endif"
                                                style="width: {{ $programa['porcentaje'] }}%"
                                            ></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 min-w-[3rem] text-right">
                                            {{ $programa['porcentaje'] }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No hay datos disponibles con los filtros seleccionados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($programas) > 0)
                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <td colspan="9" class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <strong>Total de programas:</strong> {{ count($programas) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-filament::section>
    </div>

    {{-- Script para exportar a CSV --}}
    @push('scripts')
    <script>
        function exportTableToCSV(filename) {
            const table = document.getElementById('tabla-programas');
            let csv = [];

            // Obtener encabezados
            const headers = [];
            table.querySelectorAll('thead tr th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            csv.push(headers.join(','));

            // Obtener filas
            table.querySelectorAll('tbody tr').forEach(tr => {
                const row = [];
                tr.querySelectorAll('td').forEach(td => {
                    // Limpiar el texto y escapar comas
                    let text = td.textContent.trim().replace(/\s+/g, ' ');
                    if (text.includes(',')) {
                        text = '"' + text + '"';
                    }
                    row.push(text);
                });
                if (row.length > 0) {
                    csv.push(row.join(','));
                }
            });

            // Crear el archivo y descargarlo
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    </script>
    @endpush
</x-filament-panels::page>
