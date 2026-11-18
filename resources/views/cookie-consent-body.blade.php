@php($settings = cookie_consent_settings())
@php($categoriesMode = $settings->mode === 'categories')
@php($categories = \JeffersonGoncalves\CookieConsent\Scripts::categoriesInUse())
@php($ledgers = collect([
    ['route' => 'lgpd-consent.store', 'key' => 'lgpd_consent_pending', 'kind' => 'consent'],
    ['route' => 'gdpr-consent.store', 'key' => 'gdpr_consent_pending', 'kind' => 'consent'],
    ['route' => 'quebec-consent.store', 'key' => 'quebec_consent_pending', 'kind' => 'consent'],
    ['route' => 'ccpa-opt-out.store', 'key' => 'ccpa_opt_out_pending', 'kind' => 'opt_out', 'rights' => ['sale_sharing']],
    ['route' => 'us-privacy-opt-out.store', 'key' => 'us_privacy_opt_out_pending', 'kind' => 'opt_out', 'rights' => ['sale', 'targeted_advertising']],
])->filter(fn (array $ledger) => \Illuminate\Support\Facades\Route::has($ledger['route']))
    ->map(fn (array $ledger) => ['url' => route($ledger['route'], absolute: false), 'key' => $ledger['key'], 'kind' => $ledger['kind'], 'rights' => $ledger['rights'] ?? []])
    ->values()->all())
@php($csrfToken = request()->hasSession() ? csrf_token() : null)
@php($content = [
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
])
<script @if(\Illuminate\Support\Facades\Vite::cspNonce()) nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" @endif src="{{ $categoriesMode ? $settings->preferences_js_url : $settings->js_url }}"
        data-cfasync="false"></script>
<script @if(\Illuminate\Support\Facades\Vite::cspNonce()) nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" @endif>
@if($settings->block_scripts || $categoriesMode || $ledgers !== [])
@include('cookie-consent::partials.state')
@endif
    @if($settings->block_scripts)
    // Scripts held with type="text/plain" data-category="…" run once their category is allowed.
    // A refusal after they ran takes effect on the next page load.
    window.cookieConsentActivate = function (state) {
        var queue = Array.prototype.filter.call(document.querySelectorAll('script[type="text/plain"][data-category]'), function (held) {
            if (held.cookieConsentQueued || !state[held.getAttribute('data-category')]) return false;
            held.cookieConsentQueued = true;

            return true;
        });

        (function next() {
            var held = queue.shift();
            if (!held) return;
            var script = document.createElement('script');
            Array.prototype.forEach.call(held.attributes, function (attribute) {
                if (attribute.name !== 'type' && attribute.name !== 'nonce') script.setAttribute(attribute.name, attribute.value);
            });
            // The nonce attribute reads empty once parsed; the property keeps it.
            if (held.nonce) script.nonce = held.nonce;
            script.text = held.text;
            // Inserted scripts load async: a blocking src one (no async/defer) must load before
            // the scripts after it run, as the page had it (e.g. a library, then its init).
            var blocking = held.hasAttribute('src') && !held.hasAttribute('async') && !held.hasAttribute('defer');
            if (blocking) {
                script.async = false;
                script.onload = script.onerror = next;
            }
            held.parentNode.replaceChild(script, held);
            if (!blocking) next();
        })();
    };
    @endif
    @if($ledgers !== [])
    // Record each choice in the privacy ledgers installed (laravel-lgpd-consent, laravel-gdpr-consent,
    // laravel-quebec-consent, laravel-ccpa-opt-out, laravel-us-privacy-opt-out). Per ledger: one pending slot (latest choice wins) kept until the server
    // confirms, one request at a time, so the latest choice is always recorded last and survives
    // failures and reloads. A choice is "allow"/"deny" (banner mode) or a JSON {category: bool} map.
    var privacyLedgers = @js($ledgers).map(function (ledger) {
        var memory = null, busy = false;

        function pending() {
            try { return window.localStorage.getItem(ledger.key) || memory; } catch (e) { return memory; }
        }

        function store(status) {
            memory = status;
            try { status === null ? window.localStorage.removeItem(ledger.key) : window.localStorage.setItem(ledger.key, status); } catch (e) {}
        }

        function bodies(status) {
            var state = status === 'allow' || status === 'deny' ? null : JSON.parse(status);
            if (ledger.kind === 'opt_out') {
                // Refusing cookies (or the marketing category) opts out of the sale or sharing of personal
                // information (CCPA) / of the sale and targeted advertising (US state laws).
                var optedOut = state ? !state.marketing : status !== 'allow';

                return ledger.rights.map(function (right) { return {right: right, opted_out: optedOut}; });
            }
            if (!state) return [{purpose: 'cookies', granted: status === 'allow'}];

            return Object.keys(state).map(function (category) { return {purpose: 'cookies_' + category, granted: state[category]}; });
        }

        function post(body) {
            // XSRF-TOKEN cookie first: the embedded token goes stale on cached pages.
            var xsrf = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
            var headers = {'Content-Type': 'application/json', 'Accept': 'application/json'};
            if (xsrf) {
                headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrf[1]);
            } else if (@js($csrfToken)) {
                headers['X-CSRF-TOKEN'] = @js($csrfToken);
            }

            return window.fetch(ledger.url, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: headers,
                body: JSON.stringify(body)
            }).then(function (response) {
                // Other 4xx (e.g. 422 unknown purpose) will never succeed: drop them. 419/429 may.
                return response.ok || (response.status >= 400 && response.status < 500 && response.status !== 419 && response.status !== 429);
            }, function () {
                return false;
            });
        }

        function send(status, attempt) {
            if (busy) return;
            busy = true;
            bodies(status).reduce(function (previous, body) {
                return previous.then(function (done) { return done ? post(body) : false; });
            }, Promise.resolve(true)).then(function (done) {
                busy = false;
                var latest = pending();
                if (latest !== status) {
                    if (latest) send(latest, 0);
                } else if (done) {
                    store(null);
                } else if (attempt < 2) {
                    // A newer choice may replace this one before the timer fires: then it is obsolete.
                    setTimeout(function () { if (pending() === status) send(status, attempt + 1); }, 2000 * (attempt + 1));
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
    @if($categoriesMode)
    (function () {
        var categories = @js($categories);

        function apply(accepted, record) {
            var state = window.cookieConsentState(accepted);
            @if($settings->block_scripts)
            window.cookieConsentActivate(state);
            @endif
            @if($settings->consent_mode)
            window.cookieConsentMode('update', accepted);
            @endif
            @if($ledgers !== [])
            if (record) {
                var choice = {};
                categories.forEach(function (category) { choice[category] = state[category]; });
                privacyLedgers.forEach(function (ledger) { ledger.choose(JSON.stringify(choice)); });
            }
            @endif
        }

        var optIn = @js($settings->type) === 'opt-in', initial = window.cookieConsentState(optIn ? [] : undefined);
        var config = @js([
            'mode' => $settings->type === 'opt-in' ? 'opt-in' : 'opt-out',
            // Script tags are run by cookieConsentActivate, which keeps the CSP nonce.
            'manageScriptTags' => false,
            'guiOptions' => [
                'consentModal' => ['layout' => 'box', 'position' => match ($settings->position) {
                    'top' => 'top center', 'bottom' => 'bottom center', default => str_replace('-', ' ', $settings->position),
                }],
                'preferencesModal' => ['layout' => 'box'],
            ],
            'language' => [
                'default' => 'app',
                'translations' => ['app' => [
                    'consentModal' => [
                        'title' => $content['header'],
                        'description' => e($content['message']).($content['href'] ? ' <a href="'.e($content['href']).'" target="'.e($content['target']).'" rel="noopener noreferrer nofollow">'.e($content['link']).'</a>' : ''),
                        'acceptAllBtn' => $content['allow'],
                        'acceptNecessaryBtn' => $content['deny'],
                        'showPreferencesBtn' => __('cookie-consent::preferences.manage'),
                    ],
                    'preferencesModal' => [
                        'title' => __('cookie-consent::preferences.title'),
                        'acceptAllBtn' => $content['allow'],
                        'acceptNecessaryBtn' => $content['deny'],
                        'savePreferencesBtn' => __('cookie-consent::preferences.save'),
                        'closeIconLabel' => __('cookie-consent::preferences.close'),
                        'sections' => array_map(fn (string $category) => [
                            'title' => __("cookie-consent::preferences.categories.{$category}.title"),
                            'description' => __("cookie-consent::preferences.categories.{$category}.description"),
                            'linkedCategory' => $category,
                        ], ['necessary', ...$categories]),
                    ],
                ]],
            ],
        ]);
        config.categories = {necessary: {enabled: true, readOnly: true}};
        categories.forEach(function (category) { config.categories[category] = {enabled: initial[category]}; });
        // Every page load with a saved choice, and the first choice.
        config.onConsent = function (event) { apply(event.cookie.categories, false); };
        config.onFirstConsent = function (event) { apply(event.cookie.categories, true); };
        config.onChange = function (event) { apply(event.cookie.categories, true); };

        window.CookieConsent.run(config);
        @if($settings->block_scripts)
        // No choice yet: opt-out runs the allowed categories right away.
        if (!window.CookieConsent.validConsent()) window.cookieConsentActivate(initial);
        @endif
    })();
    @else
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
        'content' => $content,
    ]);
    @if($settings->block_scripts)
    window.cookieConsentActivate(window.cookieConsentState());
    @endif
    @if($settings->consent_mode || $settings->block_scripts || $ledgers !== [])
    cookieConsentConfig.onStatusChange = function (status) {
        @if($settings->consent_mode)
        window.cookieConsentMode('update', status);
        @endif
        @if($settings->block_scripts)
        window.cookieConsentActivate(window.cookieConsentState(status));
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
    @endif
</script>
