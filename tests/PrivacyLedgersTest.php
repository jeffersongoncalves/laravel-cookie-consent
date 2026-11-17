<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

function registerLedgerRoute(string $path = 'lgpd/consent', string $name = 'lgpd-consent.store'): void
{
    Route::post($path, fn () => response()->noContent())->name($name);
    Route::getRoutes()->refreshNameLookups();
}

function registerLgpdConsentRoute(): void
{
    registerLedgerRoute();
}

/**
 * Runs the rendered body script in Node with stubbed cookieconsent, fetch and localStorage,
 * fires the given banner status changes and returns the fetch calls and the pending slot.
 *
 * @param  list<string>  $statuses  banner choices, in order
 * @param  list<int>  $responses  HTTP status per fetch call (default 204)
 * @return array{calls: list<array{0: string, 1: array<string, mixed>}>, pending: string|null}
 */
function runBodyScript(array $statuses, string $cookie = '', array $responses = [], ?string $pending = null, string $key = 'lgpd_consent_pending'): array
{
    preg_match_all('/<script[^>]*>(.*?)<\/script>/s', view('cookie-consent::cookie-consent-body')->render(), $m);

    $js = 'var window = globalThis; var calls = []; var responses = '.json_encode($responses).';'
        .'var document = {cookie: '.json_encode($cookie).'};'
        .'var store = {}; '.($pending ? 'store['.json_encode($key).'] = '.json_encode($pending).';' : '')
        .'window.localStorage = {getItem: function (k) { return k in store ? store[k] : null; },'
        .' setItem: function (k, v) { store[k] = v; }, removeItem: function (k) { delete store[k]; }};'
        .'window.fetch = function (url, init) { calls.push([url, init]); var s = responses.shift() || 204;'
        .' return Promise.resolve({ok: s < 300, status: s}); };'
        .'window.cookieConsentMode = function () {};'
        .'window.cookieconsent = {initialise: function (c) { window.cfg = c; }};'
        .$m[1][1]
        .implode('', array_map(fn (string $s) => 'window.cfg.onStatusChange('.json_encode($s).');', $statuses))
        // Let the promise chains settle, then stop before the 2s retry timers.
        .'setTimeout(function () { console.log(JSON.stringify({calls: calls, pending: store['.json_encode($key).'] || null})); process.exit(0); }, 50);';

    return json_decode((new Process(['node', '-e', $js]))->mustRun()->getOutput(), true);
}

/**
 * @param  array{calls: list<array{0: string, 1: array<string, mixed>}>}  $result
 * @return list<bool>
 */
function grantedValues(array $result): array
{
    return array_map(fn (array $call) => json_decode($call[1]['body'], true)['granted'], $result['calls']);
}

it('does not post anywhere without a privacy ledger package', function () {
    expect(view('cookie-consent::cookie-consent-body')->render())
        ->not->toContain('lgpd/consent')
        ->not->toContain('gdpr/consent')
        ->not->toContain('ccpa/opt-out')
        ->not->toContain('onStatusChange');
});

it('posts banner choices to the lgpd consent ledger when its route exists', function () {
    registerLgpdConsentRoute();

    expect(view('cookie-consent::cookie-consent-body')->render())
        ->toContain('onStatusChange')
        ->toContain('lgpd_consent_pending')
        ->toContain("purpose: 'cookies'");
});

describe('ledger requests (runs the script in Node)', function () {
    beforeEach(function () {
        if ((new ExecutableFinder)->find('node') === null) {
            $this->markTestSkipped('node is not installed');
        }

        registerLgpdConsentRoute();
    });

    it('sends allow and deny with the XSRF cookie, keepalive, and clears the pending slot', function () {
        $result = runBodyScript(['allow'], 'XSRF-TOKEN=abc%3D');
        [[$url, $init]] = $result['calls'];

        expect($url)->toBe('/lgpd/consent')
            ->and($init['keepalive'])->toBeTrue()
            ->and($init['headers']['X-XSRF-TOKEN'])->toBe('abc=')
            ->and(json_decode($init['body'], true))->toBe(['purpose' => 'cookies', 'granted' => true])
            ->and($result['pending'])->toBeNull()
            ->and(grantedValues(runBodyScript(['deny'])))->toBe([false])
            ->and(runBodyScript(['deny'])['calls'][0][1]['headers'])->not->toHaveKeys(['X-XSRF-TOKEN', 'X-CSRF-TOKEN']);
    });

    it('ignores dismiss', function () {
        expect(runBodyScript(['dismiss'])['calls'])->toBe([]);
    });

    it('records quick successive choices in order, the latest last', function () {
        // deny is chosen while allow is in flight: it waits, then goes out after it.
        expect(grantedValues(runBodyScript(['allow', 'deny'])))->toBe([true, false]);
    });

    it('keeps a failed choice pending and resends it on the next page load', function () {
        expect(runBodyScript(['allow'], responses: [500])['pending'])->toBe('allow')
            ->and(runBodyScript(['allow'], responses: [419])['pending'])->toBe('allow');

        $next = runBodyScript([], pending: 'allow');

        expect(grantedValues($next))->toBe([true])
            ->and($next['pending'])->toBeNull();
    });

    it('drops a choice the server rejects for good', function () {
        expect(runBodyScript(['allow'], responses: [422])['pending'])->toBeNull();
    });
});

describe('gdpr and ccpa ledgers (runs the script in Node)', function () {
    beforeEach(function () {
        if ((new ExecutableFinder)->find('node') === null) {
            $this->markTestSkipped('node is not installed');
        }
    });

    it('records the choice as a cookies consent in laravel-gdpr-consent', function () {
        registerLedgerRoute('gdpr/consent', 'gdpr-consent.store');

        [[$url, $init]] = runBodyScript(['allow'], key: 'gdpr_consent_pending')['calls'];

        expect($url)->toBe('/gdpr/consent')
            ->and(json_decode($init['body'], true))->toBe(['purpose' => 'cookies', 'granted' => true])
            ->and(runBodyScript(['allow'], responses: [500], key: 'gdpr_consent_pending')['pending'])->toBe('allow');
    });

    it('records deny as a sale and sharing opt-out in laravel-ccpa-opt-out', function () {
        registerLedgerRoute('ccpa/opt-out', 'ccpa-opt-out.store');

        [[$url, $init]] = runBodyScript(['deny'], key: 'ccpa_opt_out_pending')['calls'];

        expect($url)->toBe('/ccpa/opt-out')
            ->and(json_decode($init['body'], true))->toBe(['right' => 'sale_sharing', 'opted_out' => true])
            ->and(json_decode(runBodyScript(['allow'], key: 'ccpa_opt_out_pending')['calls'][0][1]['body'], true))
            ->toBe(['right' => 'sale_sharing', 'opted_out' => false]);
    });

    it('sends each choice to every ledger installed', function () {
        registerLedgerRoute();
        registerLedgerRoute('gdpr/consent', 'gdpr-consent.store');
        registerLedgerRoute('ccpa/opt-out', 'ccpa-opt-out.store');

        expect(array_column(runBodyScript(['allow'])['calls'], 0))
            ->toBe(['/lgpd/consent', '/gdpr/consent', '/ccpa/opt-out']);
    });
});
