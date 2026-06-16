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
            ->brandName('Medlicar')
            // Logo de l'agence : dépose le fichier dans public/images/logo.png
            // (s'affichera automatiquement ; sinon le nom "Medlicar" est utilisé).
            ->brandLogo(fn () => file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : null)
            ->brandLogoHeight('2.5rem')
            ->favicon(fn () => file_exists(public_path('images/favicon.png')) ? asset('images/favicon.png') : null)
            ->login()
            // Couleurs du drapeau algérien (vert / rouge ; le blanc est le fond clair)
            ->colors([
                'primary' => Color::hex('#006233'), // vert
                'success' => Color::hex('#006233'),
                'danger' => Color::hex('#D21034'),  // rouge
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->navigationGroups([
                'Réservations',
                'Clients',
                'Parc',
                'Tarifs',
                'Finances',
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
