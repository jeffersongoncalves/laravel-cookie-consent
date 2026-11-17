{{--
    The cookie table of your cookie policy: this app's own cookies plus those of the
    jeffersongoncalves analytics and chat packages installed, grouped by category.
    @include('cookie-consent::policy', ['cookies' => [[name, provider, purpose, lifetime, category], ...]])
    adds your own rows (lifetime: "1 year", "30 minutes" or "session"; category: necessary,
    analytics, marketing or functionality).
--}}
@php
    use Carbon\CarbonInterval;
    use Illuminate\Support\Facades\Route;
    use JeffersonGoncalves\CookieConsent\Scripts;

    $settings = cookie_consent_settings();
    $lifetime = fn (string $value): string => $value === 'session'
        ? __('cookie-consent::policy.session')
        : CarbonInterval::fromString($value)->cascade()->locale(app()->getLocale())->forHumans(['parts' => 2]);
    $site = __('cookie-consent::policy.this_site');
    $sessionLifetime = config('session.expire_on_close') ? 'session' : config('session.lifetime', 120).' minutes';

    $rows = [
        [(string) config('session.cookie'), $site, __('cookie-consent::policy.purposes.session'), $sessionLifetime, 'necessary'],
        ['XSRF-TOKEN', $site, __('cookie-consent::policy.purposes.xsrf'), $sessionLifetime, 'necessary'],
        $settings->mode === 'categories'
            ? ['cc_cookie', $site, __('cookie-consent::policy.purposes.consent'), '6 months', 'necessary']
            : ['cookieconsent_status', $site, __('cookie-consent::policy.purposes.consent'), '1 year', 'necessary'],
    ];

    foreach (['lgpd-consent' => 'lgpd_consent_id', 'gdpr-consent' => 'gdpr_consent_id', 'ccpa-opt-out' => 'ccpa_opt_out_id'] as $ledger => $cookie) {
        if (Route::has("{$ledger}.store")) {
            $rows[] = [(string) config("{$ledger}.route.cookie", $cookie), $site, __('cookie-consent::policy.purposes.ledger'), '13 months', 'necessary'];
        }
    }

    foreach (Scripts::installed() as $id) {
        foreach (Scripts::CATALOG[$id]['cookies'] as [$name, $duration, $purpose]) {
            $rows[] = [$name, Scripts::CATALOG[$id]['provider'], __("cookie-consent::policy.purposes.{$purpose}"), $duration, Scripts::assigned($id) ?? 'necessary'];
        }
    }

    $groups = collect([...$rows, ...($cookies ?? [])])->groupBy(4);
@endphp
<div class="cookie-policy">
    @foreach(['necessary', ...Scripts::CATEGORIES] as $category)
        @continue(! $groups->has($category))
        <section class="cookie-policy-category">
            <h3>{{ __("cookie-consent::preferences.categories.{$category}.title") }}</h3>
            <p>{{ __("cookie-consent::preferences.categories.{$category}.description") }}</p>
            <table>
                <thead>
                <tr>
                    <th scope="col">{{ __('cookie-consent::policy.columns.cookie') }}</th>
                    <th scope="col">{{ __('cookie-consent::policy.columns.provider') }}</th>
                    <th scope="col">{{ __('cookie-consent::policy.columns.purpose') }}</th>
                    <th scope="col">{{ __('cookie-consent::policy.columns.lifetime') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($groups[$category] as [$name, $provider, $purpose, $duration])
                    <tr>
                        <td><code>{{ $name }}</code></td>
                        <td>{{ $provider }}</td>
                        <td>{{ $purpose }}</td>
                        <td>{{ $lifetime($duration) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @endforeach
    @if($settings->mode === 'categories')
        <p><button type="button" data-cc="show-preferencesModal">{{ __('cookie-consent::policy.change') }}</button></p>
    @endif
</div>
