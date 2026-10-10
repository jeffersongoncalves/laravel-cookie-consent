<?php

use Illuminate\Support\Facades\Vite;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

function enableConsentMode(string $type): void
{
    $settings = cookie_consent_settings();
    $settings->type = $type;
    $settings->consent_mode = true;
    $settings->save();
}

/**
 * Runs the rendered head script in Node with the given cookie, optionally
 * followed by a banner status change, and returns the dataLayer entries.
 *
 * @return list<array<int, mixed>>
 */
function runConsentScript(?string $cookie, ?string $statusChange = null): array
{
    preg_match('/<script[^>]*>(.*?)<\/script>/s', view('cookie-consent::cookie-consent-head')->render(), $m);

    $js = 'var window = globalThis; var document = {cookie: '.json_encode((string) $cookie).'};'
        .$m[1]
        .($statusChange ? 'window.cookieConsentMode("update", '.json_encode($statusChange).');' : '')
        .'console.log(JSON.stringify(dataLayer.map(function (a) { return Array.from(a); })));';

    $process = new Process(['node', '-e', $js]);
    $process->mustRun();

    return json_decode($process->getOutput(), true);
}

it('passes the compliance type to cookieconsent', function () {
    enableConsentMode('opt-in');

    expect(view('cookie-consent::cookie-consent-body')->render())
        ->toContain('opt-in')
        ->toContain('onStatusChange')
        ->toContain("cookieConsentMode('update', status)");
});

it('stamps the CSP nonce on the consent mode script', function () {
    enableConsentMode('opt-in');
    Vite::useCspNonce('test-nonce');

    preg_match_all('/<script\b[^>]*>/', view('cookie-consent::cookie-consent-head')->render(), $tags);

    expect($tags[0])->toHaveCount(1)->each->toContain('nonce="test-nonce"');
});

describe('consent state (runs the script in Node)', function () {
    beforeEach(function () {
        if ((new ExecutableFinder)->find('node') === null) {
            $this->markTestSkipped('node is not installed');
        }
    });

    it('denies everything by default on opt-in and grants on allow', function () {
        enableConsentMode('opt-in');

        [$default, $update] = runConsentScript(null, 'allow');

        expect($default)->toBe(['consent', 'default', [
            'ad_storage' => 'denied', 'ad_user_data' => 'denied', 'ad_personalization' => 'denied',
            'analytics_storage' => 'denied', 'wait_for_update' => 500,
        ]])->and($update[1])->toBe('update')
            ->and(array_unique(array_values($update[2])))->toBe(['granted']);
    });

    it('restores a previous choice from the cookieconsent cookie', function () {
        enableConsentMode('opt-in');

        expect(runConsentScript('foo=bar; cookieconsent_status=allow')[0][2]['analytics_storage'])->toBe('granted')
            ->and(runConsentScript('cookieconsent_status=deny')[0][2]['analytics_storage'])->toBe('denied')
            ->and(runConsentScript('cookieconsent_status=dismiss')[0][2]['analytics_storage'])->toBe('denied');
    });

    it('grants by default on opt-out and info, and denies on deny', function (string $type) {
        enableConsentMode($type);

        [$default, $update] = runConsentScript(null, 'deny');

        expect($default[2]['ad_storage'])->toBe('granted')
            ->and($update[2]['ad_storage'])->toBe('denied');
    })->with(['opt-out', 'info']);
});
