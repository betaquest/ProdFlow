<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\DashboardGeneral::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render(<<<'HTML'
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
                            });
                    };
                </script>
            HTML)
        );
    }
}
