<?php

use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;

it('returns the settings instance via the helper', function () {
    expect(cookie_consent_settings())->toBeInstanceOf(CookieConsentSettings::class);
});

it('belongs to the cookie_consent group', function () {
    expect(CookieConsentSettings::group())->toBe('cookie_consent');
});

it('registers the settings class in the settings config', function () {
    expect(config('settings.settings'))->toContain(CookieConsentSettings::class);
});

it('registers all 23 settings keys via the migration', function () {
    expect(DB::table('settings')->where('group', 'cookie_consent')->count())->toBe(23);
});

it('renders the head view with the stylesheet link', function () {
    $view = view('cookie-consent::cookie-consent-head')->render();

    expect($view)
        ->toContain('<link rel="stylesheet"')
        ->toContain(cookie_consent_settings()->css_url);
});

it('renders the body view with the cookieconsent initialiser', function () {
    $view = view('cookie-consent::cookie-consent-body')->render();

    expect($view)
        ->toContain('cookieconsent.initialise')
        ->toContain('JSON.parse')
        ->toContain(cookie_consent_settings()->js_url);
});
