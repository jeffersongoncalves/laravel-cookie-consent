<?php

use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\CookieConsent\Scripts;

afterEach(fn () => Scripts::detectInstalledUsing(null));

/** @return array<string, list<list<string>>> category title => rows of cells */
function policyTables(array $data = []): array
{
    $html = view('cookie-consent::policy', $data)->render();
    preg_match_all('/<h3>(.*?)<\/h3>.*?<tbody>(.*?)<\/tbody>/s', $html, $sections, PREG_SET_ORDER);

    return collect($sections)->mapWithKeys(function (array $section) {
        preg_match_all('/<tr>(.*?)<\/tr>/s', $section[2], $rows);

        return [html_entity_decode($section[1]) => array_map(function (string $row) {
            preg_match_all('/<td>(.*?)<\/td>/s', $row, $cells);

            return array_map(fn (string $cell) => html_entity_decode(strip_tags($cell)), $cells[1]);
        }, $rows[1])];
    })->all();
}

it('lists the app cookies as strictly necessary', function () {
    Scripts::detectInstalledUsing(fn () => false);
    config(['session.cookie' => 'app_session', 'session.lifetime' => 120]);

    expect(policyTables())->toBe(['Strictly necessary' => [
        ['app_session', 'This website', 'Keeps your session, such as staying signed in.', '2 hours'],
        ['XSRF-TOKEN', 'This website', 'Protects forms against cross-site request forgery.', '2 hours'],
        ['cookieconsent_status', 'This website', 'Remembers your cookie choices.', '1 year'],
    ]]);
});

it('adds the cookies of the installed packages under their category, the privacy ledgers and extra rows', function () {
    Scripts::detectInstalledUsing(fn (string $id) => in_array($id, ['pixel', 'tawk-to', 'plausible'], true));
    Route::post('gdpr/consent', fn () => response()->noContent())->name('gdpr-consent.store');
    Route::getRoutes()->refreshNameLookups();

    $tables = policyTables(['cookies' => [['theme', 'This website', 'Remembers the dark mode.', '1 year', 'functionality']]]);

    expect(array_keys($tables))->toBe(['Strictly necessary', 'Marketing', 'Functionality'])
        ->and(end($tables['Strictly necessary']))->toBe(['gdpr_consent_id', 'This website', 'Links your privacy choices to this browser.', '1 year 1 month'])
        ->and($tables['Marketing'])->toBe([['_fbp', 'Meta Pixel', 'Measures ad conversions and builds advertising audiences.', '3 months']])
        ->and(array_column($tables['Functionality'], 0))->toBe(['__tawkuuid', 'TawkConnectionTime', 'theme'])
        ->and($tables['Functionality'][1][3])->toBe('Session');
});

it('translates the table and the lifetimes', function () {
    Scripts::detectInstalledUsing(fn (string $id) => $id === 'hotjar');
    app()->setLocale('pt_BR');

    $tables = policyTables();

    expect(array_keys($tables))->toBe(['Estritamente necessários', 'Análise'])
        ->and($tables['Análise'][1][3])->toBe('30 minutos');
});

it('offers to change the choice in categories mode', function () {
    $settings = cookie_consent_settings();
    $settings->mode = 'categories';
    $settings->save();

    expect(view('cookie-consent::policy')->render())
        ->toContain('data-cc="show-preferencesModal"')
        ->toContain('cc_cookie');
});
