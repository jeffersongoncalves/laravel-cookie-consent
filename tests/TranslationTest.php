<?php

use Illuminate\Support\Facades\DB;

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
