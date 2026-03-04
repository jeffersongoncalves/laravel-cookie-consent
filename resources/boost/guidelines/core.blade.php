## Laravel Cookie Consent

### Overview
Laravel Cookie Consent provides GDPR/CCPA-compliant cookie consent banners powered by the cookieconsent JavaScript library. All settings are stored in the database via `spatie/laravel-settings`, making them manageable from admin panels without code changes.

**Namespace:** `JeffersonGoncalves\CookieConsent`
**Service Provider:** `CookieConsentServiceProvider` (auto-discovered)

### Key Concepts
- **Settings-driven:** All banner configuration is stored in the database via `CookieConsentSettings` (spatie/laravel-settings).
- **Two Blade includes:** `cookie-consent-head` for CSS and `cookie-consent-body` for JS initialization.
- **Helper function:** `cookie_consent_settings()` returns the settings instance from the container.
- **Auto-registered:** The settings class is automatically appended to `config('settings.settings')`.

### Settings Properties

The `CookieConsentSettings` class (group: `cookie_consent`) has these properties:

**Assets:**
- `css_url` (string) -- URL for cookieconsent CSS
- `js_url` (string) -- URL for cookieconsent JS

**Content strings:**
- `content_header`, `content_message`, `content_dismiss`, `content_allow`, `content_deny`
- `content_link`, `content_href` (nullable), `content_close`, `content_target`, `content_policy`

**Palette colors:**
- `popup_background`, `popup_text`, `popup_link`
- `button_background`, `button_border`, `button_text`
- `highlight_background`, `highlight_border`, `highlight_text`

**Layout:**
- `position` (string) -- Banner position (e.g., `bottom`, `top`, `bottom-left`)
- `theme` (string) -- Theme name (e.g., `block`, `edgeless`, `classic`)

### Blade Integration

@verbatim
<code-snippet name="blade-usage" lang="php">
<!-- In your layout's <head> section -->
@include('laravel-cookie-consent::cookie-consent-head')

<!-- Before closing </body> tag -->
@include('laravel-cookie-consent::cookie-consent-body')
</code-snippet>
@endverbatim

### Helper Function

@verbatim
<code-snippet name="helper-function" lang="php">
// Access settings anywhere in your application
$settings = cookie_consent_settings();
$position = $settings->position;
$theme = $settings->theme;
</code-snippet>
@endverbatim

### Configuration
- No config file is published. All settings live in the database.
- Settings migrations are loaded from `database/settings` and publishable with tag `cookie-consent-settings-migrations`.
- The settings class is auto-registered in `config('settings.settings')` during `packageRegistered()`.

### Conventions
- Settings group name is `cookie_consent`.
- Views are namespaced as `laravel-cookie-consent::`.
- Uses the cookieconsent JavaScript library (CDN URLs stored in settings).
- The `content_href` property is the only nullable string (for optional privacy policy link).
- Requires `spatie/laravel-settings` ^3.0 or ^4.0.
