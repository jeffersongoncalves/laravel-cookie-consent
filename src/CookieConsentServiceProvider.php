<?php

namespace JeffersonGoncalves\CookieConsent;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CookieConsentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-cookie-consent')
            ->hasConfigFile()
            ->hasViews();
    }
}
