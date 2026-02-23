@php($settings = cookie_consent_settings())
<script src="{{ $settings->js_url }}"
        data-cfasync="false"></script>
<script>
    window.cookieconsent.initialise({
        "palette": {
            "popup": {
                "background": "{{ $settings->popup_background }}",
                "link": "{{ $settings->popup_link }}",
                "text": "{{ $settings->popup_text }}"
            },
            "button": {
                "background": "{{ $settings->button_background }}",
                "border": "{{ $settings->button_border }}",
                "text": "{{ $settings->button_text }}"
            },
            "highlight": {
                "background": "{{ $settings->highlight_background }}",
                "border": "{{ $settings->highlight_border }}",
                "text": "{{ $settings->highlight_text }}"
            }
        },
        "position": "{{ $settings->position }}",
        "theme": "{{ $settings->theme }}",
        "content": {
            "header": "{{ __('cookie-consent::default.header') }}",
            "message": "{{ __('cookie-consent::default.message') }}",
            "dismiss": "{{ __('cookie-consent::default.dismiss') }}",
            "allow": "{{ __('cookie-consent::default.allow') }}",
            "deny": "{{ __('cookie-consent::default.deny') }}",
            "link": "{{ __('cookie-consent::default.link') }}",
            "href": "{{ $settings->content_href }}",
            "close": "{{ $settings->content_close }}",
            "target": "{{ __('cookie-consent::default.target') }}",
            "policy": "{{ __('cookie-consent::default.policy') }}"
        }
    });
</script>
