<?php

use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;
use Spatie\LaravelSettings\SettingsCache;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

function settings_cache(): SettingsCache
{
    config(['settings.cache.enabled' => true, 'settings.cache.store' => 'array']);

    return app(SettingsCacheFactory::class)->build(CookieConsentSettings::repository());
}

it('runs every settings migration from scratch with the cache enabled', function () {
    $cache = settings_cache();
    $this->setUpDatabase();

    $this->runSettingsMigration();

    expect($cache->get(CookieConsentSettings::class))
        ->mode->toBe('banner')
        ->block_scripts->toBeFalse();
});

it('refreshes a cached pre-4.6 copy when adding the script blocking settings', function () {
    $cache = settings_cache();
    $stale = cookie_consent_settings();
    $stale->block_scripts = true;
    $cache->put($stale);
    DB::table('settings')->where('group', 'cookie_consent')
        ->whereIn('name', ['mode', 'block_scripts', 'script_categories', 'honor_gpc', 'preferences_css_url', 'preferences_js_url'])
        ->delete();

    (require __DIR__.'/../database/settings/2026_11_17_000000_add_script_blocking_to_cookie_consent_settings.php')->up();

    expect($cache->get(CookieConsentSettings::class)->block_scripts)->toBeFalse()
        ->and(cookie_consent_settings()->mode)->toBe('banner');
});
