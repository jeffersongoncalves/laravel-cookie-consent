<?php

use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('cookie_consent', function (SettingsBlueprint $blueprint): void {
            // All off by default: upgrading changes nothing until you opt in.
            $blueprint->add('mode', 'banner');
            $blueprint->add('block_scripts', false);
            $blueprint->add('script_categories', []);
            $blueprint->add('honor_gpc', false);
            $blueprint->add('preferences_css_url', 'https://cdn.jsdelivr.net/npm/vanilla-cookieconsent@3.1.0/dist/cookieconsent.css');
            $blueprint->add('preferences_js_url', 'https://cdn.jsdelivr.net/npm/vanilla-cookieconsent@3.1.0/dist/cookieconsent.umd.js');
        });

        // The migrator writes the repository only; without this, a cache with no TTL keeps
        // serving a copy without the new properties. Every migration that adds properties
        // ends with this block, so the last one to run leaves a complete cached copy.
        $cache = app(SettingsCacheFactory::class)->build(CookieConsentSettings::repository());

        if ($cache->isEnabled()) {
            try {
                $cache->put(new CookieConsentSettings);
            } catch (MissingSettings) {
                $cache->clear(); // a later migration adds the remaining properties
            }
        }

        app()->forgetInstance(CookieConsentSettings::class);
    }
};
