<?php

namespace JeffersonGoncalves\CookieConsent;

use Closure;
use Composer\InstalledVersions;
use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;

/**
 * The scripts of the jeffersongoncalves analytics and chat packages: their cookie category
 * and the cookies they set. Each package asks for its <script> attributes through
 * cookie_consent_script_attributes('<id>'), so none of them depends on this package.
 */
class Scripts
{
    public const CATEGORIES = ['analytics', 'marketing', 'functionality'];

    /**
     * category null = sets no cookies, never blocked by default.
     * cookies: [name, lifetime ("2 years", "30 minutes", "session"), purpose key in policy.purposes].
     *
     * @var array<string, array{provider: string, category: string|null, cookies: list<array{0: string, 1: string, 2: string}>}>
     */
    public const CATALOG = [
        'gtag' => ['provider' => 'Google Analytics', 'category' => 'analytics', 'cookies' => [['_ga', '2 years', 'visitors'], ['_ga_<container-id>', '2 years', 'visitors']]],
        'gtm' => ['provider' => 'Google Tag Manager', 'category' => 'marketing', 'cookies' => []],
        'pixel' => ['provider' => 'Meta Pixel', 'category' => 'marketing', 'cookies' => [['_fbp', '3 months', 'ads']]],
        'clarity' => ['provider' => 'Microsoft Clarity', 'category' => 'analytics', 'cookies' => [['_clck', '1 year', 'recordings'], ['_clsk', '1 day', 'recordings'], ['CLID', '1 year', 'recordings']]],
        'hotjar' => ['provider' => 'Hotjar', 'category' => 'analytics', 'cookies' => [['_hjSessionUser_<site-id>', '1 year', 'recordings'], ['_hjSession_<site-id>', '30 minutes', 'recordings']]],
        'mixpanel' => ['provider' => 'Mixpanel', 'category' => 'analytics', 'cookies' => [['mp_<token>_mixpanel', '1 year', 'product']]],
        'amplitude' => ['provider' => 'Amplitude', 'category' => 'analytics', 'cookies' => [['AMP_<api-key>', '1 year', 'product']]],
        'matomo' => ['provider' => 'Matomo', 'category' => 'analytics', 'cookies' => [['_pk_id.<site-id>', '13 months', 'visitors'], ['_pk_ses.<site-id>', '30 minutes', 'visitors']]],
        'crisp' => ['provider' => 'Crisp', 'category' => 'functionality', 'cookies' => [['crisp-client/session/<website-id>', '6 months', 'chat']]],
        'tawk-to' => ['provider' => 'Tawk.to', 'category' => 'functionality', 'cookies' => [['__tawkuuid', '6 months', 'chat'], ['TawkConnectionTime', 'session', 'chat']]],
        'plausible' => ['provider' => 'Plausible', 'category' => null, 'cookies' => []],
        'fathom' => ['provider' => 'Fathom', 'category' => null, 'cookies' => []],
        'simple-analytics' => ['provider' => 'Simple Analytics', 'category' => null, 'cookies' => []],
        'pirsch' => ['provider' => 'Pirsch', 'category' => null, 'cookies' => []],
        'cloudflare-web-analytics' => ['provider' => 'Cloudflare Web Analytics', 'category' => null, 'cookies' => []],
        'goatcounter' => ['provider' => 'GoatCounter', 'category' => null, 'cookies' => []],
        'umami' => ['provider' => 'Umami', 'category' => null, 'cookies' => []],
    ];

    /** Google tags already follow the banner through Consent Mode when it is on. */
    private const CONSENT_MODE_AWARE = ['gtag', 'gtm'];

    /** @var (Closure(string): bool)|null */
    private static ?Closure $installed = null;

    /** The category a script waits for, or null when it runs right away. */
    public static function category(string $id): ?string
    {
        $settings = app(CookieConsentSettings::class);

        if (! $settings->block_scripts || ($settings->consent_mode && in_array($id, self::CONSENT_MODE_AWARE, true))) {
            return null;
        }

        return self::assigned($id);
    }

    /** The category of a script regardless of blocking: settings override first, then the catalog. */
    public static function assigned(string $id): ?string
    {
        $override = app(CookieConsentSettings::class)->script_categories[$id] ?? null;

        if (is_string($override)) {
            return in_array($override, self::CATEGORIES, true) ? $override : null;
        }

        return self::CATALOG[$id]['category'] ?? null;
    }

    /** ' type="text/plain" data-category="…"' for a blocked script, '' otherwise. */
    public static function attributes(string $id): string
    {
        $category = self::category($id);

        return $category === null ? '' : ' type="text/plain" data-category="'.e($category).'"';
    }

    /**
     * Ids of the catalog packages installed in the app.
     *
     * @return list<string>
     */
    public static function installed(): array
    {
        $check = self::$installed ?? fn (string $id): bool => InstalledVersions::isInstalled("jeffersongoncalves/laravel-{$id}");

        return array_values(array_filter(array_keys(self::CATALOG), $check));
    }

    /**
     * Categories offered in the preferences modal: those of the installed packages,
     * or analytics and marketing when none of them is installed.
     *
     * @return list<string>
     */
    public static function categoriesInUse(): array
    {
        $used = array_unique(array_filter(array_map(self::assigned(...), self::installed())));

        return array_values(array_intersect(self::CATEGORIES, $used ?: ['analytics', 'marketing']));
    }

    /** @param  (Closure(string): bool)|null  $check */
    public static function detectInstalledUsing(?Closure $check): void
    {
        self::$installed = $check;
    }
}
