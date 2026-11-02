@php($settings = cookie_consent_settings())
<script @if(\Illuminate\Support\Facades\Vite::cspNonce()) nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" @endif src="{{ $settings->js_url }}"
        data-cfasync="false"></script>
<script @if(\Illuminate\Support\Facades\Vite::cspNonce()) nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" @endif>
    var cookieConsentConfig = @js([
        'type' => $settings->type,
        'palette' => [
            'popup' => [
                'background' => $settings->popup_background,
                'link' => $settings->popup_link,
                'text' => $settings->popup_text,
            ],
            'button' => [
                'background' => $settings->button_background,
                'border' => $settings->button_border,
                'text' => $settings->button_text,
            ],
            'highlight' => [
                'background' => $settings->highlight_background,
                'border' => $settings->highlight_border,
                'text' => $settings->highlight_text,
            ],
        ],
        'position' => $settings->position,
        'theme' => $settings->theme,
        'showLink' => $settings->content_href !== null,
        // cookieconsent's defaults minus their English aria-labels, so the translated visible text is the accessible name.
        'elements' => [
            'messagelink' => '<span id="cookieconsent:desc" class="cc-message">@{{message}} <a role=button tabindex="0" class="cc-link" href="@{{href}}" rel="noopener noreferrer nofollow" target="@{{target}}">@{{link}}</a></span>',
            'dismiss' => '<a role=button tabindex="0" class="cc-btn cc-dismiss">@{{dismiss}}</a>',
            'allow' => '<a role=button tabindex="0" class="cc-btn cc-allow">@{{allow}}</a>',
            'deny' => '<a role=button tabindex="0" class="cc-btn cc-deny">@{{deny}}</a>',
            'link' => '<a role=button tabindex="0" class="cc-link" href="@{{href}}" rel="noopener noreferrer nofollow" target="@{{target}}">@{{link}}</a>',
        ],
        'content' => [
            'header' => $settings->content_header ?? __('cookie-consent::banner.header'),
            'message' => $settings->content_message ?? __('cookie-consent::banner.message'),
            'dismiss' => $settings->content_dismiss ?? __('cookie-consent::banner.dismiss'),
            'allow' => $settings->content_allow ?? __('cookie-consent::banner.allow'),
            'deny' => $settings->content_deny ?? __('cookie-consent::banner.deny'),
            'link' => $settings->content_link ?? __('cookie-consent::banner.link'),
            'href' => $settings->content_href,
            'close' => $settings->content_close,
            'target' => $settings->content_target,
            'policy' => $settings->content_policy ?? __('cookie-consent::banner.policy'),
        ],
    ]);
    @if($settings->consent_mode)
    cookieConsentConfig.onStatusChange = function (status) {
        window.cookieConsentMode('update', status);
    };
    @endif
    window.cookieconsent.initialise(cookieConsentConfig);
</script>
