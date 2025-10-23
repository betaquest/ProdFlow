<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProdFlow - Dashboards</title>
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    @livewireStyles
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950">
    <div class="min-h-full">
        {{-- HEADER --}}
        <header class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    {{-- Logo y Título --}}
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="h-12 w-auto">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                ProdFlow
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Sistema de Gestión de Producción
                            </p>
                        </div>
                    </div>

                    {{-- Botón de Admin --}}
                    <div>
                        @auth
                            <a href="{{ route('filament.admin.pages.dashboard') }}"
                               class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-black dark:text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Panel de Administración
                            </a>
                        @else
                            <a href="{{ route('filament.admin.auth.login') }}"
                               class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-black dark:text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Iniciar Sesión
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        {{-- MAIN CONTENT --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            {{-- Título de la sección --}}
            <div class="text-center mb-12">
                <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">
                    Let's get started
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Selecciona un dashboard para visualizar el estado de producción en tiempo real
                </p>
            </div>

            {{-- Lista de Dashboards --}}
            @if($dashboards->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($dashboards as $dashboard)
                        <a href="{{ route('dashboards.show', $dashboard->slug) }}"
                           class="group relative bg-white dark:bg-gray-900 rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-800 hover:border-blue-500 dark:hover:border-blue-500">

                            {{-- Color de fondo si está definido --}}
                            @if($dashboard->color_fondo)
                                <div class="absolute inset-0 opacity-5" style="background-color: {{ $dashboard->color_fondo }};"></div>
                            @endif

                            <div class="relative p-6">
                                {{-- Icono --}}
                                <div class="flex items-center justify-center w-16 h-16 mb-4 bg-blue-100 dark:bg-blue-900/20 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>

                                {{-- Título --}}
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ $dashboard->nombre }}
                                </h3>

                                {{-- Descripción --}}
                                @if($dashboard->descripcion)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                        {{ $dashboard->descripcion }}
                                    </p>
                                @endif

                                {{-- Detalles --}}
                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                    @if($dashboard->mostrar_reloj)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Reloj</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>{{ $dashboard->tiempo_actualizacion }}s</span>
                                    </div>
                                </div>

                                {{-- Flecha --}}
                                <div class="absolute bottom-4 right-4 text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                {{-- Estado vacío --}}
                <div class="text-center py-16">
                    <svg class="mx-auto h-24 w-24 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                        No hay dashboards disponibles
                    </h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Contacta al administrador para crear dashboards.
                    </p>
                </div>
            @endif
        </main>

        {{-- FOOTER --}}
        <footer class="mt-16 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                    <p>&copy; {{ date('Y') }} ProdFlow. Sistema de Gestión de Producción.</p>
                    <p class="mt-1">Actualización en tiempo real cada {{ $dashboards->first()?->tiempo_actualizacion ?? 30 }} segundos</p>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
