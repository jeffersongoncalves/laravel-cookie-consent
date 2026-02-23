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
            "header": "{{ $settings->content_header }}",
            "message": "{{ $settings->content_message }}",
            "dismiss": "{{ $settings->content_dismiss }}",
            "allow": "{{ $settings->content_allow }}",
            "deny": "{{ $settings->content_deny }}",
            "link": "{{ $settings->content_link }}",
            "href": "{{ $settings->content_href }}",
            "close": "{{ $settings->content_close }}",
            "target": "{{ $settings->content_target }}",
            "policy": "{{ $settings->content_policy }}"
        }
    });
</script>
