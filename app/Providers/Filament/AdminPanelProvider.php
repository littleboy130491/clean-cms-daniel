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
use Awcodes\Curator\CuratorPlugin;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Illuminate\Validation\Rules\Password;
use Outerweb\FilamentTranslatableFields\Filament\Plugins\FilamentTranslatableFieldsPlugin;
use Filament\Navigation\NavigationGroup;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Illuminate\Support\Facades\Gate;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use Filament\Forms\Components\TextInput;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
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
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
                DoNotCacheResponse::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandName(config('cms.site_name'))
            // ->brandLogo(config('cms.site_logo'))
            ->favicon(config('cms.site_favicon'))
            ->theme(asset('css/filament/admin/theme.css'))
            ->plugins([
                CuratorPlugin::make(),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterNavigation: true,
                        navigationGroup: 'Users'
                    )
                    ->passwordUpdateRules(
                        rules: [Password::default()->mixedCase()->uncompromised(3)], // you may pass an array of validation rules as well. (default = ['min:8'])
                        requiresCurrentPassword: true, // when false, the user can update their password without entering their current password. (default = true)
                    ),
                FilamentTranslatableFieldsPlugin::make(),
                FilamentShieldPlugin::make(),
                FilamentMenuBuilderPlugin::make()
                    ->showCustomTextPanel()
                    ->addLocations($this->getMenuLocations())
                    ->addMenuItemFields([
                        TextInput::make('classes'),
                    ]),
                FilamentSpatieLaravelBackupPlugin::make()
                    ->authorize(fn(): bool => auth()->user()->hasRole(['admin', 'super_admin'])),
            ])
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Contents')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make()
                    ->label('Users')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make()
                    ->label('Profile')
                    ->icon('heroicon-o-cog-6-tooth'),
                NavigationGroup::make()
                    ->label('Settings')
                    ->icon('heroicon-o-cog-6-tooth'),
            ]);
    }

    public function boot(): void
    {
        Gate::policy(\Awcodes\Curator\Models\Media::class, \App\Policies\MediaPolicy::class);
        Gate::policy(\Datlechin\FilamentMenuBuilder\Models\Menu::class, \App\Policies\MenuPolicy::class);
    }

    private function getMenuLocations(): array
    {
        $languages = config('cms.language_available', []);
        $locations = [];

        $baseLocations = config('cms.navigation_menu_locations', [
            'header' => 'Header',
            'footer' => 'Footer',
        ]);

        foreach ($baseLocations as $key => $label) {
            foreach ($languages as $langCode => $langName) {
                $locations["{$key}_{$langCode}"] = "{$label} ({$langName})";
            }
        }

        return $locations;
    }
}