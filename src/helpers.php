<?php

use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;

if (! function_exists('cookie_consent_settings')) {
    function cookie_consent_settings(): CookieConsentSettings
    {
        return app(CookieConsentSettings::class);
    }
}
