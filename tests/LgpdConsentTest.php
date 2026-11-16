<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

function registerLgpdConsentRoute(): void
{
    Route::post('lgpd/consent', fn () => response()->noContent())->name('lgpd-consent.store');
    Route::getRoutes()->refreshNameLookups();
}

/**
 * Runs the rendered body script in Node with stubbed cookieconsent/fetch, fires a banner
 * status change and returns the fetch calls.
 *
 * @return list<array{0: string, 1: array<string, mixed>}>
 */
function runBodyScript(string $status, string $cookie = ''): array
{
    preg_match_all('/<script[^>]*>(.*?)<\/script>/s', view('cookie-consent::cookie-consent-body')->render(), $m);

    $js = 'var window = globalThis; var calls = []; var document = {cookie: '.json_encode($cookie).'};'
        .'window.fetch = function (url, init) { calls.push([url, init]); };'
        .'window.cookieConsentMode = function () {};'
        .'window.cookieconsent = {initialise: function (c) { window.cfg = c; }};'
        .$m[1][1]
        .'window.cfg.onStatusChange('.json_encode($status).');'
        .'console.log(JSON.stringify(calls));';

    return json_decode((new Process(['node', '-e', $js]))->mustRun()->getOutput(), true);
}

it('does not post anywhere without laravel-lgpd-consent', function () {
    expect(view('cookie-consent::cookie-consent-body')->render())
        ->not->toContain('lgpd/consent')
        ->not->toContain('onStatusChange');
});

it('posts banner choices to the lgpd consent ledger when its route exists', function () {
    registerLgpdConsentRoute();

    expect(view('cookie-consent::cookie-consent-body')->render())
        ->toContain('onStatusChange')
        ->toContain("'\/lgpd\/consent'")
        ->toContain("purpose: 'cookies'");
});

describe('ledger requests (runs the script in Node)', function () {
    beforeEach(function () {
        if ((new ExecutableFinder)->find('node') === null) {
            $this->markTestSkipped('node is not installed');
        }

        registerLgpdConsentRoute();
    });

    it('sends allow and deny with the XSRF cookie', function () {
        [[$url, $init]] = runBodyScript('allow', 'XSRF-TOKEN=abc%3D');

        expect($url)->toBe('/lgpd/consent')
            ->and($init['headers']['X-XSRF-TOKEN'])->toBe('abc=')
            ->and(json_decode($init['body'], true))->toBe(['purpose' => 'cookies', 'granted' => true])
            ->and(json_decode(runBodyScript('deny')[0][1]['body'], true)['granted'])->toBeFalse()
            ->and(runBodyScript('deny')[0][1]['headers'])->not->toHaveKeys(['X-XSRF-TOKEN', 'X-CSRF-TOKEN']);
    });

    it('ignores dismiss', function () {
        expect(runBodyScript('dismiss'))->toBe([]);
    });
});
