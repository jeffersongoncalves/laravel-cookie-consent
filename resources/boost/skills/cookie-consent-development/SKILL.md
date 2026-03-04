---
name: cookie-consent-development
description: Development guide for laravel-cookie-consent, a GDPR/CCPA cookie consent banner package using spatie/laravel-settings for database-driven configuration.
---

# Cookie Consent Development Skill

## When to use this skill

- When developing or extending the laravel-cookie-consent package
- When adding new settings properties to the cookie consent banner
- When modifying the Blade views for the consent banner
- When integrating cookie consent settings into a Filament admin panel
- When writing settings migrations for cookie consent defaults
- When debugging cookie consent display or initialization issues

## Setup

### Requirements
- PHP 8.2+
- Laravel 11, 12, or 13
- `spatie/laravel-settings` ^3.0 or ^4.0
- `spatie/laravel-package-tools` ^1.14

### Installation

```bash
composer require jeffersongoncalves/laravel-cookie-consent
```

### Run Migrations

```bash
php artisan migrate
```

The package auto-loads settings migrations from its `database/settings` directory.

### Publish Migrations (Optional)

```bash
php artisan vendor:publish --tag=cookie-consent-settings-migrations
```

## Package Structure

```
src/
  CookieConsentServiceProvider.php   # Registers views, settings, migrations
  Settings/
    CookieConsentSettings.php        # Spatie Settings class (group: cookie_consent)
  helpers.php                        # Global cookie_consent_settings() helper
resources/
  views/
    cookie-consent-head.blade.php    # CSS include for <head>
    cookie-consent-body.blade.php    # JS initialization for <body>
database/
  settings/                          # Settings migrations (auto-loaded)
```

## Features

### CookieConsentSettings Class

The settings class extends `Spatie\LaravelSettings\Settings` with group `cookie_consent`:

```php
use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;

// All properties:
$settings = app(CookieConsentSettings::class);

// Asset URLs
$settings->css_url;    // string - cookieconsent CSS CDN URL
$settings->js_url;     // string - cookieconsent JS CDN URL

// Content strings
$settings->content_header;   // string
$settings->content_message;  // string - main banner message
$settings->content_dismiss;  // string - dismiss button text
$settings->content_allow;    // string - allow button text
$settings->content_deny;     // string - deny button text
$settings->content_link;     // string - "Learn more" link text
$settings->content_href;     // ?string - privacy policy URL (nullable)
$settings->content_close;    // string - close button text
$settings->content_target;   // string - link target (_blank, _self)
$settings->content_policy;   // string - policy text

// Popup palette colors
$settings->popup_background;      // string - hex color
$settings->popup_text;            // string - hex color
$settings->popup_link;            // string - hex color

// Button palette colors
$settings->button_background;     // string - hex color
$settings->button_border;         // string - hex color
$settings->button_text;           // string - hex color

// Highlight palette colors
$settings->highlight_background;  // string - hex color
$settings->highlight_border;      // string - hex color
$settings->highlight_text;        // string - hex color

// Layout
$settings->position;   // string - e.g., 'bottom', 'top', 'bottom-left', 'bottom-right'
$settings->theme;       // string - e.g., 'block', 'edgeless', 'classic'
```

### Helper Function

```php
// Globally available helper
$settings = cookie_consent_settings();

// Equivalent to:
$settings = app(CookieConsentSettings::class);
```

### Blade Views

#### Head View (CSS)

Include in your layout's `<head>` section:

```blade
@include('laravel-cookie-consent::cookie-consent-head')
```

This renders a `<link>` tag pointing to `$settings->css_url`.

#### Body View (JS Initialization)

Include before closing `</body>` tag:

```blade
@include('laravel-cookie-consent::cookie-consent-body')
```

This renders:
1. A `<script>` tag loading the cookieconsent library from `$settings->js_url`
2. An initialization script calling `window.cookieconsent.initialise()` with palette, position, theme, and content from settings

### Service Provider Registration

The `CookieConsentServiceProvider` does three things:

1. **configurePackage()**: Registers the package name and views
2. **packageRegistered()**: Auto-appends `CookieConsentSettings::class` to `config('settings.settings')` array
3. **packageBooted()**: Loads settings migrations and publishes them with tag `cookie-consent-settings-migrations`

```php
// Settings auto-registration (no manual config needed)
$settings = Config::get('settings.settings', []);
$settings[] = CookieConsentSettings::class;
Config::set('settings.settings', $settings);
```

## Configuration

There is no config file. All configuration is managed through the `cookie_consent` settings group in the database.

### Creating a Settings Migration

```php
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateCookieConsentSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('cookie_consent.css_url', 'https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css');
        $this->migrator->add('cookie_consent.js_url', 'https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js');
        $this->migrator->add('cookie_consent.content_header', 'Cookies used on the website!');
        $this->migrator->add('cookie_consent.content_message', 'This website uses cookies to ensure you get the best experience.');
        $this->migrator->add('cookie_consent.content_dismiss', 'Got it!');
        $this->migrator->add('cookie_consent.content_allow', 'Allow cookies');
        $this->migrator->add('cookie_consent.content_deny', 'Decline');
        $this->migrator->add('cookie_consent.content_link', 'Learn more');
        $this->migrator->add('cookie_consent.content_href', null);
        $this->migrator->add('cookie_consent.content_close', '&#x274c;');
        $this->migrator->add('cookie_consent.content_target', '_blank');
        $this->migrator->add('cookie_consent.content_policy', 'Cookie Policy');
        $this->migrator->add('cookie_consent.popup_background', '#000');
        $this->migrator->add('cookie_consent.popup_text', '#fff');
        $this->migrator->add('cookie_consent.popup_link', '#fff');
        $this->migrator->add('cookie_consent.button_background', '#f1d600');
        $this->migrator->add('cookie_consent.button_border', 'transparent');
        $this->migrator->add('cookie_consent.button_text', '#000');
        $this->migrator->add('cookie_consent.highlight_background', '#f1d600');
        $this->migrator->add('cookie_consent.highlight_border', 'transparent');
        $this->migrator->add('cookie_consent.highlight_text', '#000');
        $this->migrator->add('cookie_consent.position', 'bottom');
        $this->migrator->add('cookie_consent.theme', 'block');
    }
}
```

## Testing Patterns

### Testing Settings Access

```php
use JeffersonGoncalves\CookieConsent\Settings\CookieConsentSettings;

it('returns settings instance via helper', function () {
    $settings = cookie_consent_settings();

    expect($settings)->toBeInstanceOf(CookieConsentSettings::class);
});

it('has correct settings group', function () {
    expect(CookieConsentSettings::group())->toBe('cookie_consent');
});
```

### Testing Blade Views

```php
use function Pest\Laravel\get;

it('renders cookie consent head view', function () {
    $view = view('laravel-cookie-consent::cookie-consent-head')->render();

    expect($view)->toContain('<link rel="stylesheet"');
});

it('renders cookie consent body view', function () {
    $view = view('laravel-cookie-consent::cookie-consent-body')->render();

    expect($view)
        ->toContain('cookieconsent.initialise')
        ->toContain('"palette"')
        ->toContain('"position"')
        ->toContain('"theme"');
});
```

### Testing Service Provider Registration

```php
it('registers settings class in config', function () {
    $settings = config('settings.settings');

    expect($settings)->toContain(CookieConsentSettings::class);
});
```

### Running Tests

```bash
# Static analysis
vendor/bin/phpstan analyse

# Code formatting
vendor/bin/pint
```

## Adding New Settings Properties

1. Add the property to `CookieConsentSettings`:

```php
public string $new_property;
```

2. Create a new settings migration:

```php
$this->migrator->add('cookie_consent.new_property', 'default_value');
```

3. Update the Blade views if the property affects rendering.

4. Run migrations:

```bash
php artisan migrate
```

## Customizing Views

Publish views for customization:

```bash
php artisan vendor:publish --tag=laravel-cookie-consent-views
```

Views will be published to `resources/views/vendor/laravel-cookie-consent/`.
