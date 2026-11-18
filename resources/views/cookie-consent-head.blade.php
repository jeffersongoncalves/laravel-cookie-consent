@php($settings = cookie_consent_settings())
<link rel="stylesheet" type="text/css"
      href="{{ $settings->mode === 'categories' ? $settings->preferences_css_url : $settings->css_url }}"/>
@if($settings->consent_mode)
{{-- Google Consent Mode v2: must run before the GTM/gtag snippets so their tags start with the right state. --}}
<script @if(\Illuminate\Support\Facades\Vite::cspNonce()) nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" @endif>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
@include('cookie-consent::partials.state')
    window.cookieConsentMode = function (command, status) {
        // analytics_storage follows the analytics category, the ad signals the marketing one.
        var state = window.cookieConsentState(status);
        var ads = state.marketing ? 'granted' : 'denied', analytics = state.analytics ? 'granted' : 'denied';
        var consent = {ad_storage: ads, ad_user_data: ads, ad_personalization: ads, analytics_storage: analytics};
        if (command === 'default') consent.wait_for_update = 500;
        gtag('consent', command, consent);
    };
    window.cookieConsentMode('default');
</script>
@endif
