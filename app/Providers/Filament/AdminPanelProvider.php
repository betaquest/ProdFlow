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
use Illuminate\Support\Facades\Auth;
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
            ->brandLogo(asset('logo_h1.png'))
            ->darkModeBrandLogo(asset('logo_h1_dark.png'))
            ->brandLogoHeight('2.5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->homeUrl(function () {
                // Si es Administrador, ir al Dashboard
                if (Auth::check() && Auth::user()->hasRole('Administrador')) {
                    return '/admin';
                }
                // Para otros usuarios, ir a Mis Fases
                return '/admin/mis-fases';
            })
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\DashboardGeneral::class,
            ])
            ->userMenuItems([
                'area' => \Filament\Navigation\MenuItem::make()
                    ->label(fn () => Auth::user()?->area?->nombre ?? 'Sin área asignada')
                    ->icon('heroicon-o-building-office-2')
                    ->url(null),
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

                    // Fix para modals/slideovers que desaparecen
                    document.addEventListener('DOMContentLoaded', function() {
                        // Observar cambios en el DOM para detectar modales que se ocultan incorrectamente
                        const observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                mutation.addedNodes.forEach((node) => {
                                    if (node.nodeType === 1 && node.hasAttribute && node.hasAttribute('x-data')) {
                                        // Forzar re-inicialización de Alpine.js en modales
                                        if (typeof Alpine !== 'undefined') {
                                            Alpine.initTree(node);
                                        }
                                    }
                                });
                            });
                        });

                        observer.observe(document.body, {
                            childList: true,
                            subtree: true
                        });

                        // Fix específico para slideovers/modals de Filament
                        document.addEventListener('livewire:navigated', () => {
                            // Reinicializar Alpine.js después de navegación de Livewire
                            if (typeof Alpine !== 'undefined') {
                                Alpine.start();
                            }
                        });

                        // Fix para backdrop oscuro que se queda sin modal
                        // Solo limpiar cuando se cierra un modal, no constantemente
                        const cleanupOrphanBackdrops = () => {
                            setTimeout(() => {
                                // Buscar todos los dialogos
                                const allDialogs = document.querySelectorAll('[role="dialog"]');

                                allDialogs.forEach(dialog => {
                                    // Verificar si tiene contenido de modal visible
                                    const hasModalContent = dialog.querySelector('.fi-modal-content, .fi-slideover-content, .fi-modal-window, .fi-slideover-window');

                                    // Si no tiene contenido visible, es un backdrop huérfano
                                    if (!hasModalContent) {
                                        const computedStyle = window.getComputedStyle(dialog);
                                        // Solo remover si está realmente vacío
                                        if (computedStyle.display !== 'none' && dialog.children.length === 0) {
                                            console.log('Removiendo backdrop huérfano');
                                            dialog.remove();
                                        }
                                    }
                                });

                                // Restaurar overflow si no hay diálogos
                                const remainingDialogs = document.querySelectorAll('[role="dialog"]');
                                if (remainingDialogs.length === 0) {
                                    document.body.style.overflow = '';
                                    document.documentElement.style.overflow = '';
                                }
                            }, 300);
                        };

                        // Listener para cerrar modal con ESC
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') {
                                cleanupOrphanBackdrops();
                            }
                        });

                        // Click fuera del modal para cerrar
                        document.addEventListener('click', (e) => {
                            if (e.target.closest('.fi-modal-close-overlay')) {
                                cleanupOrphanBackdrops();
                            }
                        });
                    });
                </script>
            HTML)
        );
    }
}
