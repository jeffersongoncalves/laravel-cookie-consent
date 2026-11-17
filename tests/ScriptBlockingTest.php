<?php

use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\CookieConsent\Scripts;
use Symfony\Component\Process\ExecutableFinder;

/** @param  array<string, mixed>  $values */
function cookieConsentSettings(array $values): void
{
    $settings = cookie_consent_settings();
    foreach ($values as $key => $value) {
        $settings->{$key} = $value;
    }
    $settings->save();
}

/**
 * Runs the head and body scripts in Node against a fake DOM holding one blocked script per
 * category, then $then (JS), and returns what ran, the ledger calls, the dataLayer and the
 * config handed to the categories banner.
 *
 * @param  list<array{category: string, src?: string, async?: bool}>|null  $held  default: one inline script per category
 * @return array{ran: list<array{category: string, nonce: string, type: string|null, src: string|null}>, calls: list<array{0: string, 1: array<string, mixed>}>, dataLayer: list<mixed>, cc: array<string, mixed>|null}
 */
function runConsentPage(string $cookie = '', string $then = '', bool $gpc = false, ?array $held = null): array
{
    $scripts = fn (string $view): string => implode('', array_filter(
        (preg_match_all('/<script[^>]*>(.*?)<\/script>/s', view($view)->render(), $m) ? $m[1] : []),
        fn (string $js) => trim($js) !== '',
    ));

    $js = 'var window = globalThis, ran = [], calls = [];'
        .($gpc ? 'Object.defineProperty(globalThis, "navigator", {value: {globalPrivacyControl: true}, configurable: true});' : '')
        .'var held = '.json_encode($held ?? [['category' => 'analytics'], ['category' => 'marketing'], ['category' => 'functionality']]).'.map(function (spec) {'
        .'  var attributes = [{name: "type", value: "text/plain"}, {name: "data-category", value: spec.category}, {name: "nonce", value: ""}];'
        .'  if (spec.src) attributes.push({name: "src", value: spec.src});'
        .'  if (spec.async) attributes.push({name: "async", value: ""});'
        .'  var node = {nonce: "n-" + spec.category, text: spec.category, attributes: attributes,'
        .'    getAttribute: function (name) { var a = attributes.filter(function (a) { return a.name === name; })[0]; return a ? a.value : null; },'
        .'    hasAttribute: function (name) { return node.getAttribute(name) !== null; }};'
        .'  node.parentNode = {replaceChild: function (script) { held.splice(held.indexOf(node), 1); window.lastScript = script;'
        .'    ran.push({category: script.attrs["data-category"], nonce: script.nonce, type: script.attrs.type || null, src: script.attrs.src || null}); }};'
        .'  return node; });'
        .'var document = {cookie: '.json_encode($cookie).', querySelectorAll: function () { return held.slice(); },'
        .'  createElement: function () { return {attrs: {}, setAttribute: function (n, v) { this.attrs[n] = v; }}; }};'
        .'window.localStorage = {getItem: function () { return null; }, setItem: function () {}, removeItem: function () {}};'
        .'window.fetch = function (url, init) { calls.push([url, JSON.parse(init.body)]); return Promise.resolve({ok: true, status: 204}); };'
        .'window.cookieconsent = {initialise: function (c) { window.cfg = c; }};'
        .'window.CookieConsent = {run: function (c) { window.cc = c; }, validConsent: function () { return '.json_encode($cookie !== '').'; }};'
        .$scripts('cookie-consent::cookie-consent-head')
        .$scripts('cookie-consent::cookie-consent-body')
        .$then
        .'setTimeout(function () { console.log(JSON.stringify({ran: ran, calls: calls, dataLayer: (window.dataLayer || []).map(function (a) { return Array.from(a); }),'
        .' cc: window.cc ? JSON.parse(JSON.stringify(window.cc)) : null})); process.exit(0); }, 50);';

    return json_decode(runNode($js), true);
}

/** @param  array{ran: list<array{category: string}>}  $result */
function ranCategories(array $result): array
{
    return array_column($result['ran'], 'category');
}

afterEach(fn () => Scripts::detectInstalledUsing(null));

describe('script attributes', function () {
    it('holds nothing until script blocking is on', function () {
        expect(cookie_consent_script_attributes('pixel'))->toBe('');
    });

    it('holds each script under its category, and never the cookieless ones', function () {
        cookieConsentSettings(['block_scripts' => true]);

        expect(cookie_consent_script_attributes('pixel'))->toBe(' type="text/plain" data-category="marketing"')
            ->and(cookie_consent_script_attributes('hotjar'))->toBe(' type="text/plain" data-category="analytics"')
            ->and(cookie_consent_script_attributes('crisp'))->toBe(' type="text/plain" data-category="functionality"')
            ->and(cookie_consent_script_attributes('plausible'))->toBe('')
            ->and(cookie_consent_script_attributes('unknown'))->toBe('');
    });

    it('leaves the Google tags to Consent Mode when it is on', function () {
        cookieConsentSettings(['block_scripts' => true]);
        expect(cookie_consent_script_attributes('gtm'))->toContain('marketing');

        cookieConsentSettings(['consent_mode' => true]);
        expect(cookie_consent_script_attributes('gtm'))->toBe('')
            ->and(cookie_consent_script_attributes('gtag'))->toBe('');
    });

    it('follows the category overrides, an empty one never blocking', function () {
        cookieConsentSettings(['block_scripts' => true, 'script_categories' => ['plausible' => 'analytics', 'pixel' => '', 'hotjar' => 'nonsense']]);

        expect(cookie_consent_script_attributes('plausible'))->toContain('analytics')
            ->and(cookie_consent_script_attributes('pixel'))->toBe('')
            ->and(cookie_consent_script_attributes('hotjar'))->toBe('');
    });

    it('offers the categories of the installed packages', function () {
        Scripts::detectInstalledUsing(fn (string $id) => in_array($id, ['crisp', 'plausible'], true));
        expect(Scripts::categoriesInUse())->toBe(['functionality']);

        Scripts::detectInstalledUsing(fn () => false);
        expect(Scripts::categoriesInUse())->toBe(['analytics', 'marketing']);
    });
});

describe('activation (runs the scripts in Node)', function () {
    beforeEach(function () {
        if ((new ExecutableFinder)->find('node') === null) {
            $this->markTestSkipped('node is not installed');
        }
    });

    it('runs nothing before the choice on opt-in, everything on allow, keeping the nonce', function () {
        cookieConsentSettings(['block_scripts' => true, 'type' => 'opt-in']);

        expect(runConsentPage()['ran'])->toBe([]);

        $result = runConsentPage(then: 'window.cfg.onStatusChange("allow");');

        expect(ranCategories($result))->toBe(['analytics', 'marketing', 'functionality'])
            ->and($result['ran'][0])->toBe(['category' => 'analytics', 'nonce' => 'n-analytics', 'type' => null, 'src' => null])
            ->and(ranCategories(runConsentPage('cookieconsent_status=allow')))->toHaveCount(3)
            ->and(runConsentPage('cookieconsent_status=deny')['ran'])->toBe([]);
    });

    it('waits for a blocking src script to load before running the next one', function () {
        cookieConsentSettings(['block_scripts' => true, 'type' => 'opt-in']);
        $held = [['category' => 'analytics', 'src' => 'lib.js'], ['category' => 'analytics'], ['category' => 'analytics', 'src' => 'a.js', 'async' => true], ['category' => 'analytics']];

        // allow twice: the second activation must not run the init script still waiting for lib.js.
        $waiting = runConsentPage(then: 'window.cfg.onStatusChange("allow"); window.cfg.onStatusChange("allow");', held: $held);
        $loaded = runConsentPage(then: 'window.cfg.onStatusChange("allow"); window.lastScript.onload();', held: $held);

        expect(array_column($waiting['ran'], 'src'))->toBe(['lib.js'])
            ->and(array_column($loaded['ran'], 'src'))->toBe(['lib.js', null, 'a.js', null]);
    });

    it('runs everything right away on opt-out, except marketing under Global Privacy Control', function () {
        cookieConsentSettings(['block_scripts' => true, 'type' => 'opt-out']);

        expect(ranCategories(runConsentPage()))->toHaveCount(3)
            ->and(ranCategories(runConsentPage(gpc: true)))->toHaveCount(3);

        cookieConsentSettings(['honor_gpc' => true]);

        expect(ranCategories(runConsentPage(gpc: true)))->toBe(['analytics', 'functionality']);
    });

    it('denies the ad signals of Consent Mode under Global Privacy Control', function () {
        cookieConsentSettings(['consent_mode' => true, 'type' => 'opt-out', 'honor_gpc' => true]);

        $default = runConsentPage(gpc: true)['dataLayer'][0][2];

        expect($default['ad_storage'])->toBe('denied')
            ->and($default['analytics_storage'])->toBe('granted');
    });
});

describe('categories mode (runs the scripts in Node)', function () {
    beforeEach(function () {
        if ((new ExecutableFinder)->find('node') === null) {
            $this->markTestSkipped('node is not installed');
        }

        Scripts::detectInstalledUsing(fn (string $id) => in_array($id, ['hotjar', 'pixel'], true));
    });

    it('loads the preferences banner instead of the classic one', function () {
        cookieConsentSettings(['mode' => 'categories']);

        expect(view('cookie-consent::cookie-consent-head')->render())->toContain('vanilla-cookieconsent@3.1.0/dist/cookieconsent.css')
            ->and(view('cookie-consent::cookie-consent-body')->render())
            ->toContain('vanilla-cookieconsent@3.1.0/dist/cookieconsent.umd.js')
            ->not->toContain('window.cookieconsent.initialise');
    });

    it('configures one toggle per category in use, translated, off until chosen on opt-in', function () {
        cookieConsentSettings(['mode' => 'categories', 'type' => 'opt-in']);
        app()->setLocale('pt_BR');

        $cc = runConsentPage()['cc'];

        expect($cc['mode'])->toBe('opt-in')
            ->and($cc['manageScriptTags'])->toBeFalse()
            ->and($cc['categories'])->toBe([
                'necessary' => ['enabled' => true, 'readOnly' => true],
                'analytics' => ['enabled' => false],
                'marketing' => ['enabled' => false],
            ])
            ->and(array_column($cc['language']['translations']['app']['preferencesModal']['sections'], 'title'))
            ->toBe(['Estritamente necessários', 'Análise', 'Marketing'])
            ->and($cc['language']['translations']['app']['consentModal']['showPreferencesBtn'])->toBe('Gerenciar preferências');
    });

    it('runs the accepted categories and updates Consent Mode per category', function () {
        cookieConsentSettings(['mode' => 'categories', 'type' => 'opt-in', 'block_scripts' => true, 'consent_mode' => true]);

        $result = runConsentPage(then: 'window.cc.onConsent({cookie: {categories: ["necessary", "analytics"]}});');
        $update = end($result['dataLayer'])[2];

        expect(ranCategories($result))->toBe(['analytics'])
            ->and($update['analytics_storage'])->toBe('granted')
            ->and($update['ad_storage'])->toBe('denied');
    });

    it('runs the default categories before the choice on opt-out', function () {
        cookieConsentSettings(['mode' => 'categories', 'type' => 'opt-out', 'block_scripts' => true, 'honor_gpc' => true]);

        expect(ranCategories(runConsentPage(gpc: true)))->toBe(['analytics', 'functionality'])
            ->and(runConsentPage(gpc: true)['cc']['categories']['marketing'])->toBe(['enabled' => false]);
    });

    it('records each category in the ledgers on the first choice and on changes, not on every page', function () {
        cookieConsentSettings(['mode' => 'categories', 'type' => 'opt-in']);
        Route::post('lgpd/consent', fn () => response()->noContent())->name('lgpd-consent.store');
        Route::post('ccpa/opt-out', fn () => response()->noContent())->name('ccpa-opt-out.store');
        Route::getRoutes()->refreshNameLookups();

        $first = runConsentPage(then: 'window.cc.onFirstConsent({cookie: {categories: ["necessary", "analytics"]}});');

        expect($first['calls'])->toBe([
            ['/lgpd/consent', ['purpose' => 'cookies_analytics', 'granted' => true]],
            ['/ccpa/opt-out', ['right' => 'sale_sharing', 'opted_out' => true]],
            ['/lgpd/consent', ['purpose' => 'cookies_marketing', 'granted' => false]],
        ])->and(runConsentPage('cc_cookie=x', 'window.cc.onConsent({cookie: {categories: ["necessary"]}});')['calls'])->toBe([])
            ->and(runConsentPage(then: 'window.cc.onChange({cookie: {categories: ["necessary", "marketing"]}});')['calls'])->toContain(
                ['/ccpa/opt-out', ['right' => 'sale_sharing', 'opted_out' => false]],
            );
    });
});
