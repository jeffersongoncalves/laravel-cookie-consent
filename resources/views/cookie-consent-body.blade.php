<script src="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js" data-cfasync="false"></script>
<script>
    window.cookieconsent.initialise({
        "palette": {
            "popup": {
                "background": "{{ config('cookie-consent.palette.popup.background') }}",
                "link": "{{ config('cookie-consent.palette.popup.link') }}",
                "text": "{{ config('cookie-consent.palette.popup.text') }}"
            },
            "button": {
                "background": "{{ config('cookie-consent.palette.button.background') }}",
                "border": "{{ config('cookie-consent.palette.button.border') }}",
                "text": "{{ config('cookie-consent.palette.button.text') }}"
            },
            "highlight": {
                "background": "{{ config('cookie-consent.palette.highlight.background') }}",
                "border": "{{ config('cookie-consent.palette.highlight.border') }}",
                "text": "{{ config('cookie-consent.palette.highlight.text') }}"
            }
        },
        "position": "{{ config('cookie-consent.position') }}",
        "theme": "{{ config('cookie-consent.theme') }}",
        "revokable": {{ config('cookie-consent.revokable') }},
        "location": {{ config('cookie-consent.location') }},
        "content": {
            "header": "{{ __('cookie-consent::default.header') }}",
            "message": "{{ __('cookie-consent::default.message') }}",
            "dismiss": "{{ __('cookie-consent::default.dismiss') }}",
            "allow": "{{ __('cookie-consent::default.allow') }}",
            "deny": "{{ __('cookie-consent::default.deny') }}",
            "link": "{{ __('cookie-consent::default.link') }}",
            "href": "{{ config('cookie-consent.content.href') }}",
            "close": "{{ config('cookie-consent.content.close') }}",
            "target": "{{ __('cookie-consent::default.target') }}",
            "policy": "{{ __('cookie-consent::default.policy') }}"
        }
    });
</script>
