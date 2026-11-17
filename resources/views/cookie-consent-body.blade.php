@php($settings = cookie_consent_settings())
@php($ledgers = collect([
    ['route' => 'lgpd-consent.store', 'key' => 'lgpd_consent_pending', 'kind' => 'consent'],
    ['route' => 'gdpr-consent.store', 'key' => 'gdpr_consent_pending', 'kind' => 'consent'],
    ['route' => 'ccpa-opt-out.store', 'key' => 'ccpa_opt_out_pending', 'kind' => 'opt_out'],
])->filter(fn (array $ledger) => \Illuminate\Support\Facades\Route::has($ledger['route']))
    ->map(fn (array $ledger) => ['url' => route($ledger['route'], absolute: false), 'key' => $ledger['key'], 'kind' => $ledger['kind']])
    ->values()->all())
@php($csrfToken = request()->hasSession() ? csrf_token() : null)
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
    @if($ledgers !== [])
    // Record each choice in the privacy ledgers installed (laravel-lgpd-consent, laravel-gdpr-consent,
    // laravel-ccpa-opt-out). Per ledger: one pending slot (latest choice wins) kept until the server
    // confirms, one request at a time, so the latest choice is always recorded last and survives
    // failures and reloads.
    var privacyLedgers = @js($ledgers).map(function (ledger) {
        var memory = null, busy = false;

        function pending() {
            try { return window.localStorage.getItem(ledger.key) || memory; } catch (e) { return memory; }
        }

        function store(status) {
            memory = status;
            try { status === null ? window.localStorage.removeItem(ledger.key) : window.localStorage.setItem(ledger.key, status); } catch (e) {}
        }

        function body(status) {
            // CCPA: denying cookies opts out of the sale or sharing of personal information.
            return ledger.kind === 'opt_out'
                ? {right: 'sale_sharing', opted_out: status !== 'allow'}
                : {purpose: 'cookies', granted: status === 'allow'};
        }

        function send(status, attempt) {
            if (busy) return;
            busy = true;
            // XSRF-TOKEN cookie first: the embedded token goes stale on cached pages.
            var xsrf = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
            var headers = {'Content-Type': 'application/json', 'Accept': 'application/json'};
            if (xsrf) {
                headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrf[1]);
            } else if (@js($csrfToken)) {
                headers['X-CSRF-TOKEN'] = @js($csrfToken);
            }
            window.fetch(ledger.url, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: headers,
                body: JSON.stringify(body(status))
            }).then(function (response) {
                // Other 4xx (e.g. 422 unknown purpose) will never succeed: drop them. 419/429 may.
                return response.ok || (response.status >= 400 && response.status < 500 && response.status !== 419 && response.status !== 429);
            }, function () {
                return false;
            }).then(function (done) {
                busy = false;
                var latest = pending();
                if (latest !== status) {
                    if (latest) send(latest, 0);
                } else if (done) {
                    store(null);
                } else if (attempt < 2) {
                    setTimeout(function () { send(status, attempt + 1); }, 2000 * (attempt + 1));
                }
                // Still failing: stays pending and is retried on the next page load.
            });
        }

        return {
            choose: function (status) { store(status); send(status, 0); },
            resume: function () { var status = pending(); if (status) send(status, 0); }
        };
    });
    privacyLedgers.forEach(function (ledger) { ledger.resume(); });
    @endif
    @if($settings->consent_mode || $ledgers !== [])
    cookieConsentConfig.onStatusChange = function (status) {
        @if($settings->consent_mode)
        window.cookieConsentMode('update', status);
        @endif
        @if($ledgers !== [])
        // "dismiss" is not a choice.
        if (status === 'allow' || status === 'deny') {
            privacyLedgers.forEach(function (ledger) { ledger.choose(status); });
        }
        @endif
    };
    @endif
    window.cookieconsent.initialise(cookieConsentConfig);
</script>
