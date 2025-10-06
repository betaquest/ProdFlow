<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $dashboard->nombre }}</title>
    <meta http-equiv="refresh" content="30"> {{-- refresco automático cada 30s --}}
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">

    <h1 class="text-3xl font-bold text-center mt-4 mb-6">{{ $dashboard->nombre }}</h1>

    <div class="overflow-x-auto px-6">
        <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
            <thead class="bg-gray-200 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left">Cliente</th>
                    <th class="px-3 py-2 text-left">Proyecto</th>
                    <th class="px-3 py-2 text-left">Programa</th>
                    @foreach(\App\Models\Fase::all() as $fase)
                        <th class="px-3 py-2 text-center">{{ $fase->nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($programas as $programa)
                    <tr class="{{ $loop->even ? 'bg-gray-100 dark:bg-gray-800' : 'bg-white dark:bg-gray-900' }}">
                        <td class="px-3 py-2">{{ $programa->proyecto->cliente->nombre }}</td>
                        <td class="px-3 py-2">{{ $programa->proyecto->nombre }}</td>
                        <td class="px-3 py-2">{{ $programa->nombre }}</td>
                        @foreach(\App\Models\Fase::all() as $fase)
                            @php
                                $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                                $icon = match($avance?->estado) {
                                    'done' => '✅',
                                    'progress' => '⏳',
                                    default => '⬜',
                                };
                            @endphp
                            <td class="text-center text-lg">{{ $icon }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $dashboard->nombre }}</title>
    <meta http-equiv="refresh" content="30"> {{-- refresco automático cada 30s --}}
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">

    <h1 class="text-3xl font-bold text-center mt-4 mb-6">{{ $dashboard->nombre }}</h1>

    <div class="overflow-x-auto px-6">
        <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
            <thead class="bg-gray-200 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left">Cliente</th>
                    <th class="px-3 py-2 text-left">Proyecto</th>
                    <th class="px-3 py-2 text-left">Programa</th>
                    @foreach(\App\Models\Fase::all() as $fase)
                        <th class="px-3 py-2 text-center">{{ $fase->nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($programas as $programa)
                    <tr class="{{ $loop->even ? 'bg-gray-100 dark:bg-gray-800' : 'bg-white dark:bg-gray-900' }}">
                        <td class="px-3 py-2">{{ $programa->proyecto->cliente->nombre }}</td>
                        <td class="px-3 py-2">{{ $programa->proyecto->nombre }}</td>
                        <td class="px-3 py-2">{{ $programa->nombre }}</td>
                        @foreach(\App\Models\Fase::all() as $fase)
                            @php
                                $avance = $programa->avances->firstWhere('fase_id', $fase->id);
                                $icon = match($avance?->estado) {
                                    'done' => '✅',
                                    'progress' => '⏳',
                                    default => '⬜',
                                };
                            @endphp
                            <td class="text-center text-lg">{{ $icon }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
