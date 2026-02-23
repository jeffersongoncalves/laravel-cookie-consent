<?php

namespace JeffersonGoncalves\CookieConsent\Settings;

use Spatie\LaravelSettings\Settings;

class CookieConsentSettings extends Settings
{
    public string $css_url;

    public string $js_url;

    public string $content_header;

    public string $content_message;

    public string $content_dismiss;

    public string $content_allow;

    public string $content_deny;

    public string $content_link;

    public ?string $content_href;

    public string $content_close;

    public string $content_target;

    public string $content_policy;

    public string $popup_background;

    public string $popup_text;

    public string $popup_link;

    public string $button_background;

    public string $button_border;

    public string $button_text;

    public string $highlight_background;

    public string $highlight_border;

    public string $highlight_text;

    public string $position;

    public string $theme;

    public static function group(): string
    {
        return 'cookie_consent';
    }
}
