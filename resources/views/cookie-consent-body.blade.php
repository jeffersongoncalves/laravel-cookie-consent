<script src="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js" data-cfasync="false"></script>
<script>
    window.cookieconsent.initialise({
        "palette": {
            "popup": {
                "background": "{{ config('cookie-consent.palette.popup.background') }}",
                "text": "{{ config('cookie-consent.palette.popup.text') }}"
            },
            "button": {
                "background": "{{ config('cookie-consent.palette.button.background') }}",
                "text": "{{ config('cookie-consent.palette.button.text') }}"
            }
        },
        "position": "{{ config('cookie-consent.position') }}",
        "content": {
            "message": "{{ config('cookie-consent.content.message') }}",
            "dismiss": "{{ config('cookie-consent.content.dismiss') }}",
            "link": "{{ config('cookie-consent.content.link') }}",
            "href": "{{ config('cookie-consent.content.href') }}"
        }
    });
</script>
