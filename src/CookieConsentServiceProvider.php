<?php

namespace JeffersonGoncalves\CookieConsent;

use Illuminate\Support\Facades\Config;
use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CookieConsentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-cookie-consent')
            ->hasViews()
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $settings = Config::get('settings.settings', []);
        $settings[] = CookieConsentSettings::class;
        Config::set('settings.settings', $settings);
    }

    public function packageBooted(): void
    {
        $migrationPath = __DIR__.'/../database/settings';

        $this->loadMigrationsFrom($migrationPath);

        $this->publishes([
            $migrationPath => database_path('settings'),
        ], 'cookie-consent-settings-migrations');
    }
}
