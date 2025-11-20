<?php

namespace App\Providers\Filament;

use App\Helpers\WebsiteSettingHelper;
use App\Http\Middleware\DisableBladeIconComponents;
use App\Http\Middleware\DispatchServingFilamentEvent;
use App\Http\Middleware\HomeRedirect;
use App\Http\Middleware\SpaRequired;
use App\Http\Middleware\TrustHosts;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\UseFilamentTablesTheme;
use App\Http\Widgets\Data;
use App\Http\Widgets\Statistik;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DispatchServingFilamentEvent as FilamentDispatchServingFilamentEvent;
use Filament\Http\Middleware\DisableBladeIconComponents as FilamentDisableBladeIconComponents;
use Filament\Http\Middleware\UsingSubdomain;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard as FilamentDashboard;
use Filament\Panel; 
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession as IlluminateAuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(WebsiteSettingHelper::get('site_name', 'Admin Panel'))
            ->brandLogo(fn () => WebsiteSettingHelper::getLogoUrl())
            ->brandLogoHeight('2rem')
            ->favicon(fn () => WebsiteSettingHelper::getFaviconUrl())
            // ->spa() // Dinonaktifkan untuk mengatasi masalah halaman blank di production
            // ->spaUrlExceptions([
            //     // External URLs yang tidak kompatibel dengan SPA
            //     'https://docs.filamentphp.com/*',
            //     'https://github.com/*',
            //     'https://laravel.com/*',
            //     // URL internal yang memerlukan full page reload
            //     '/admin/export/*',
            //     '/admin/download/*',
            //     '/admin/pdf/*',
            //     // URL yang menggunakan target="_blank"
            //     '/admin/external-link/*',
            //     // Pastikan endpoint diintersepsi SPA
            //     '/livewire/*',
            // ])
            ->login()
            ->registration()
            ->homeUrl('/admin/data')
            ->colors([
                // Gunakan palet brand "mountain-meadow" dari resources/css/app.css
                'primary' => [
                    50 => '#edfcf6',
                    100 => '#d3f8e8',
                    200 => '#abefd5',
                    300 => '#74e1bf',
                    400 => '#31ba93',
                    500 => '#18b18a',
                    600 => '#0c8f70',
                    700 => '#09735d',
                    800 => '#0a5b4a',
                    900 => '#094b3e',
                    950 => '#042a24',
                ],
                // Selaraskan secondary ke palet yang sama untuk konsistensi brand
                'secondary' => [
                    50 => '#edfcf6',
                    100 => '#d3f8e8',
                    200 => '#abefd5',
                    300 => '#74e1bf',
                    400 => '#31ba93',
                    500 => '#18b18a',
                    600 => '#0c8f70',
                    700 => '#09735d',
                    800 => '#0a5b4a',
                    900 => '#094b3e',
                    950 => '#042a24',
                ],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([])
            ->navigationGroups([
                'General' => NavigationGroup::make()
                    ->label('Dashboard')
                    ->icon('heroicon-o-home')
                    ->collapsed(false),
                'Products' => NavigationGroup::make()
                    ->label('Produk')
                    ->icon('heroicon-o-cube')
                    ->collapsed(false),
                'Customers' => NavigationGroup::make()
                    ->label('Pelanggan')
                    ->icon('heroicon-o-users')
                    ->collapsed(false),
                'System' => NavigationGroup::make()
                    ->label('Sistem')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(false),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                IlluminateAuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                FilamentDisableBladeIconComponents::class,
                FilamentDispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}


