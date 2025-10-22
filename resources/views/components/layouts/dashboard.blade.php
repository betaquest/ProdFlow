<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Dashboard' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    @livewireStyles

    <style>
        /* ---- GENERAL ---- */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background-color: #0f172a; /* Azul oscuro */
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        /* ---- CABECERA ---- */
        h1 {
            text-align: center;
            font-size: 3.5rem;
            margin: 1rem 0;
            letter-spacing: 2px;
        }

        /* ---- TABLA ---- 
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1.6rem;
        }
            */

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1.8rem; /* más grande */
        }


        th, td {
            padding: 0.7rem 0.9rem;
            text-align: center;
        }

        thead {
            background-color: #1e293b;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tbody tr:nth-child(even) {
            background-color: rgba(255,255,255,0.05);
        }

        tbody tr:nth-child(odd) {
            background-color: rgba(255,255,255,0.02);
        }

        /* ---- ÍCONOS ---- 
        .estado {
            font-size: 2rem;
        }
            */

        .estado {
            border-radius: 8px;
            transition: transform 0.3s;
        }
        .estado:hover {
            transform: scale(1.05);
        }

        /* ---- PIE ---- */
        footer {
            position: absolute;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 1rem;
            color: #94a3b8;
        }

        /* ---- AUTO AJUSTE ---- */
        @media (max-width: 1200px) {
            table {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 800px) {
            table {
                font-size: 1rem;
            }
        }

        /* ---- ANIMACIÓN DE ENTRADA ---- */
        tbody tr {
            transition: background-color 0.3s ease;
        }

        tbody tr:hover {
            background-color: rgba(255,255,255,0.08);
        }

        @keyframes shimmer {
            0% { background-position: -200px 0; }
            100% { background-position: 200px 0; }
        }

        .progress-bar {
            background: linear-gradient(90deg, #22c55e, #3b82f6, #22c55e);
            background-size: 400% 100%;
            animation: shimmer 6s linear infinite;
            box-shadow: 0 0 12px rgba(59,130,246,0.8);
        }
    </style>

    <script>
        // Opcional: modo pantalla completa automático
        document.addEventListener('DOMContentLoaded', () => {
            const enableFullscreen = () => {
                const el = document.documentElement;
                if (el.requestFullscreen) el.requestFullscreen();
            };
            setTimeout(enableFullscreen, 2000);
        });
    </script>
</head>

<body>
    {{ $slot }}
    @livewireScripts

    <script>
        // Interceptor global para manejar errores 419 (CSRF Token Expired)
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (status === 419) {
                        preventDefault();
                        console.warn('Sesión expirada (419). Recargando página...');
                        window.location.reload();
                    }
                });
            });
        });

        // Interceptor para peticiones fetch/axios tradicionales
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args)
                .then(response => {
                    if (response.status === 419) {
                        console.warn('Sesión expirada (419). Recargando página...');
                        window.location.reload();
                    }
                    return response;
                })
                .catch(error => {
                    // Capturar errores de red o CORS
                    throw error;
                });
        };
    </script>
</body>
</html>
