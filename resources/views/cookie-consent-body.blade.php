<script src="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js" data-cfasync="false"></script>
<script>
    window.cookieconsent.initialise({
        "palette": {
            "popup": {
                "background": "{{ config('cookieconsent.palette.popup.background') }}",
                "text": "{{ config('cookieconsent.palette.popup.text') }}"
            },
            "button": {
                "background": "{{ config('cookieconsent.palette.button.background') }}",
                "text": "{{ config('cookieconsent.palette.button.text') }}"
            }
        },
        "position": "{{ config('cookieconsent.position') }}",
        "content": {
            "message": "{{ config('cookieconsent.content.message') }}",
            "dismiss": "{{ config('cookieconsent.content.dismiss') }}",
            "link": "{{ config('cookieconsent.content.link') }}",
            "href": "{{ config('cookieconsent.content.href') }}"
        }
    });
</script>
