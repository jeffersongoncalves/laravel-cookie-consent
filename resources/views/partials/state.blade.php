{{-- JS (no <script> tag): window.cookieConsentState(status) -> {analytics, marketing, functionality}.
     status: the banner choice ("allow", "deny", "dismiss") in banner mode, the list of accepted
     categories in categories mode; undefined reads the visitor's saved choice from the cookie. --}}
@php($settings = cookie_consent_settings())
    window.cookieConsentState = window.cookieConsentState || (function (type, mode, gpc) {
        // Global Privacy Control: a request to opt out of the sale or sharing of personal information.
        gpc = gpc && typeof navigator !== 'undefined' && navigator.globalPrivacyControl === true;

        return function (status) {
            var state = {}, cookie;
            if (mode === 'categories') {
                if (status === undefined) {
                    cookie = document.cookie.match(/(?:^|;\s*)cc_cookie=([^;]*)/);
                    try { status = cookie ? JSON.parse(decodeURIComponent(cookie[1])).categories : undefined; } catch (e) { status = undefined; }
                }
                @js(\JeffersonGoncalves\CookieConsent\Scripts::CATEGORIES).forEach(function (category) {
                    // No choice yet: opt-in waits for one, opt-out runs everything until the visitor refuses.
                    state[category] = status ? status.indexOf(category) !== -1 : type !== 'opt-in';
                });
            } else {
                if (status === undefined) {
                    status = (document.cookie.match(/(?:^|;\s*)cookieconsent_status=(\w+)/) || [])[1];
                }
                // opt-in: refused until "allow". opt-out/info: allowed unless "deny".
                var allowed = status === 'allow' || (type !== 'opt-in' && status !== 'deny');
                state = {analytics: allowed, marketing: allowed, functionality: allowed};
            }
            if (gpc) state.marketing = false;

            return state;
        };
    })(@js($settings->type), @js($settings->mode), @js($settings->honor_gpc));
