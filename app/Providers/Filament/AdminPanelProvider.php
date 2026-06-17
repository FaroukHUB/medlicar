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
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->brandName(fn () => \App\Models\Agency::current()->name)
            // Logo : téléverse-le dans Paramètres → Logo (il s'affiche ici automatiquement).
            // Repli possible sur public/images/logo.png si aucun logo n'est défini.
            ->brandLogo(function () {
                $logo = \App\Models\Agency::current()->logo;
                if ($logo) {
                    return \Illuminate\Support\Facades\Storage::url($logo);
                }

                return file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : null;
            })
            ->brandLogoHeight('2.5rem')
            ->favicon(fn () => file_exists(public_path('images/favicon.png')) ? asset('images/favicon.png') : null)
            ->login()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            // Accent orange (vert/rouge conservés pour les statuts).
            ->colors([
                'primary' => Color::hex('#F97316'), // orange
                'success' => Color::hex('#16a34a'),
                'danger' => Color::hex('#D21034'),
                'warning' => Color::hex('#F59E0B'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->navigationGroups([
                'Catalogue',
                'Réservations',
                'Clients',
                'Finances',
                'Tarification',
                'Configuration',
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
}
