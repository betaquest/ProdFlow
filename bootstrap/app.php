<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejar el error 419 (CSRF Token Expired)
        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                // Si es una petición AJAX, retornar JSON con código 419
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Tu sesión ha expirado. Recargando página...',
                        'expired' => true
                    ], 419);
                }

                // Si es una petición normal, redirigir a la misma página para refrescar el token
                return back()->with('warning', 'La página se ha actualizado para renovar tu sesión.');
            }
        });
    })->create();
