<?php

use Illuminate\Support\Facades\Cache;
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

const SCRIPT_BLOCKING_SETTINGS = ['mode', 'block_scripts', 'script_categories', 'honor_gpc', 'preferences_css_url', 'preferences_js_url'];

function run_script_blocking_migration(): void
{
    DB::table('settings')->where('group', 'cookie_consent')->whereIn('name', SCRIPT_BLOCKING_SETTINGS)->delete();

    (require __DIR__.'/../database/settings/2026_11_17_000000_add_script_blocking_to_cookie_consent_settings.php')->up();
}

it('replaces a stale cached value when adding the script blocking settings', function () {
    $cache = settings_cache();
    $stale = cookie_consent_settings();
    $stale->block_scripts = true;
    $cache->put($stale);

    run_script_blocking_migration();

    expect($cache->get(CookieConsentSettings::class)->block_scripts)->toBeFalse()
        ->and(cookie_consent_settings()->mode)->toBe('banner');
});

it('refreshes a cached pre-4.6 payload without the script blocking settings', function () {
    $cache = settings_cache();

    // What 4.5 cached: the serialized settings without the six properties 4.6 adds.
    $data = array_diff_key(cookie_consent_settings()->__serialize(), array_flip(SCRIPT_BLOCKING_SETTINGS));
    $class = CookieConsentSettings::class;
    $key = (fn () => $this->resolveCacheKey($class))->call($cache);
    Cache::store('array')->put($key, 'O:'.strlen($class).':"'.$class.'"'.substr(serialize($data), 1));

    run_script_blocking_migration();

    expect($cache->get($class))
        ->mode->toBe('banner')
        ->block_scripts->toBeFalse()
        ->script_categories->toBe([])
        ->honor_gpc->toBeFalse()
        ->preferences_css_url->toBe('https://cdn.jsdelivr.net/npm/vanilla-cookieconsent@3.1.0/dist/cookieconsent.css')
        ->preferences_js_url->toBe('https://cdn.jsdelivr.net/npm/vanilla-cookieconsent@3.1.0/dist/cookieconsent.umd.js');
});
