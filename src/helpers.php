<?php

use JeffersonGoncalves\CookieConsent\Scripts;
use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;

if (! function_exists('cookie_consent_settings')) {
    function cookie_consent_settings(): CookieConsentSettings
    {
        return app(CookieConsentSettings::class);
    }
}

if (! function_exists('cookie_consent_script_attributes')) {
    /**
     * Attributes that hold a <script> until the visitor allows its cookie category
     * (' type="text/plain" data-category="analytics"'), or '' when it may run right away.
     * Packages call it behind function_exists(), so they don't depend on this one.
     */
    function cookie_consent_script_attributes(string $id): string
    {
        return Scripts::attributes($id);
    }
}
