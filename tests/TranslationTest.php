<?php

use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

function render_banner(): string
{
    return view('cookie-consent::cookie-consent-body')->render();
}

it('nulls the content settings on a fresh install', function () {
    expect(cookie_consent_settings()->content_allow)->toBeNull()
        ->and(cookie_consent_settings()->content_header)->toBeNull();
});

it('renders the banner text in the current locale', function (string $locale, string $allow) {
    app()->setLocale($locale);

    expect(render_banner())->toContain($allow);
})->with([
    'en' => ['en', 'Allow cookies'],
    'pt_BR' => ['pt_BR', 'Permitir cookies'],
]);

it('prefers a custom setting over the translation', function () {
    $settings = cookie_consent_settings();
    $settings->content_allow = 'Accept everything';
    $settings->save();
    app()->setLocale('pt_BR');

    expect(render_banner())->toContain('Accept everything')->not->toContain('Permitir cookies');
});

it('hides the learn more link when content_href is null', function () {
    expect(render_banner())->toMatch('/showLink[^,]*:false/');

    $settings = cookie_consent_settings();
    $settings->content_href = 'https://example.com/privacy';
    $settings->save();

    expect(render_banner())->toMatch('/showLink[^,]*:true/');
});

it('drops the english aria-labels so the translated text names the buttons', function () {
    expect(render_banner())
        ->toContain('{{allow}}')
        ->toContain('{{deny}}')
        ->toContain('{{dismiss}}')
        ->not->toContain('aria-label');
});

it('refreshes the cached settings after nulling the defaults', function () {
    config(['settings.cache.enabled' => true, 'settings.cache.store' => 'array']);
    $cache = app(SettingsCacheFactory::class)->build(CookieConsentSettings::repository());

    $stale = cookie_consent_settings();
    $stale->content_header = 'Cookies used on the website!';
    $cache->put($stale);

    (require __DIR__.'/../database/settings/2026_11_02_000000_make_cookie_consent_content_translatable.php')->up();

    expect($cache->get(CookieConsentSettings::class)->content_header)->toBeNull();
});

it('nulls only the values that still equal the old english defaults', function () {
    $payload = fn (string $name) => json_decode(
        DB::table('settings')->where('group', 'cookie_consent')->where('name', $name)->value('payload')
    );
    $set = fn (string $name, string $value) => DB::table('settings')
        ->where('group', 'cookie_consent')->where('name', $name)
        ->update(['payload' => json_encode($value)]);

    $set('content_header', 'Cookies used on the website!');
    $set('content_allow', 'Accept everything');

    (require __DIR__.'/../database/settings/2026_11_02_000000_make_cookie_consent_content_translatable.php')->up();

    expect($payload('content_header'))->toBeNull()
        ->and($payload('content_allow'))->toBe('Accept everything');
});
