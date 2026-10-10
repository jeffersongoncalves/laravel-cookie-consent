@php($settings = cookie_consent_settings())
<link rel="stylesheet" type="text/css"
      href="{{ $settings->css_url }}"/>
@if($settings->consent_mode)
{{-- Google Consent Mode v2: must run before the GTM/gtag snippets so their tags start with the right state. --}}
<script @if(\Illuminate\Support\Facades\Vite::cspNonce()) nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" @endif>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    window.cookieConsentMode = function (command, status) {
        // opt-in: denied until "allow". opt-out/info: granted unless "deny".
        var value = status === 'allow' || (@js($settings->type) !== 'opt-in' && status !== 'deny') ? 'granted' : 'denied';
        var state = {ad_storage: value, ad_user_data: value, ad_personalization: value, analytics_storage: value};
        if (command === 'default') state.wait_for_update = 500;
        gtag('consent', command, state);
    };
    window.cookieConsentMode('default', (document.cookie.match(/(?:^|;\s*)cookieconsent_status=(\w+)/) || [])[1]);
</script>
@endif
