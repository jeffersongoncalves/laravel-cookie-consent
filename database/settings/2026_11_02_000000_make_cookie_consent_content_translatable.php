<?php

use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Null means "use the cookie-consent::banner translation". Only values still equal
        // to the old English defaults are nulled, so customised text is kept.
        $defaults = [
            'content_header' => 'Cookies used on the website!',
            'content_message' => 'This website uses cookies to ensure you get the best experience on our website.',
            'content_dismiss' => 'Got it!',
            'content_allow' => 'Allow cookies',
            'content_deny' => 'Decline',
            'content_link' => 'Learn more',
            'content_policy' => 'Cookie Policy',
        ];

        foreach ($defaults as $name => $default) {
            $this->migrator->update("cookie_consent.{$name}", fn ($value) => $value === $default ? null : $value);
        }

        // The migrator writes the repository only; replace the cached copy with a fresh load so
        // reads and saves see the nulls. put() targets this class's key, unlike clear(), which
        // depends on the statically memoised list of discovered settings classes.
        $cache = app(SettingsCacheFactory::class)->build(CookieConsentSettings::repository());

        if ($cache->isEnabled()) {
            try {
                $cache->put(new CookieConsentSettings);
            } catch (MissingSettings) {
                // Fresh install: the class already declares properties a later migration adds,
                // so it can't load yet. Drop the cached copy; that migration caches a fresh one.
                $cache->clear();
            }
        }

        app()->forgetInstance(CookieConsentSettings::class);
    }
};
