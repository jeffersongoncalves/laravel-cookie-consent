# Changelog

All notable changes to this project will be documented in this file.

## v3.0.2 - 2026-02-24

### What's Changed

- Add Laravel 13.x support in composer.json
- Add orchestra/testbench ^11.0 for Laravel 13 testing

## v3.0.1 - 2026-02-23

### Changes

- Remove translations section from README
- Add all content settings properties to the available settings table in README

## v3.0.0 - 2026-02-23

### Breaking Changes

- **All content fields migrated from translation files to `spatie/laravel-settings`**
  - Removed all 18 translation files (`resources/lang/`)
  - Removed `hasTranslations()` from ServiceProvider
  - Content text (header, message, dismiss, allow, deny, link, target, policy) is now managed via database settings with English defaults
  

### New Settings Properties

| Property | Default |
|----------|---------|
| `content_header` | Cookies used on the website! |
| `content_message` | This website uses cookies to ensure you get the best experience on our website. |
| `content_dismiss` | Got it! |
| `content_allow` | Allow cookies |
| `content_deny` | Decline |
| `content_link` | Learn more |
| `content_target` | _blank |
| `content_policy` | Cookie Policy |

### Upgrade Guide

1. Run `php artisan migrate` to add the new settings fields
2. If you had customized translations, update the content via the settings table or a Filament settings page
3. Remove any published translation files from your app's `lang/vendor/cookie-consent/` directory

## v2.0.0 - 2026-02-22

### Breaking Changes

- Configuration migrated from `config/cookie-consent.php` to database via [spatie/laravel-settings](https://github.com/spatie/laravel-settings)
- The config file has been removed. All settings are now stored in the database.

### Migration Guide

1. Update the package:
   
   ```bash
   composer require jeffersongoncalves/laravel-cookie-consent:^2.0
   
   
   
   
   ```
2. Run migrations to create the settings in the database:
   
   ```bash
   php artisan migrate
   
   
   
   
   ```
3. Delete your old `config/cookie-consent.php` file (no longer used).
   
4. If you had custom values in the config file, update them via code:
   
   ```php
   $settings = cookie_consent_settings();
   $settings->position = 'top-right';
   $settings->popup_background = '#000000';
   $settings->save();
   
   
   
   
   ```

### What's New

- **Dynamic configuration** - All settings stored in database, changeable at runtime
- **Settings class** - `CookieConsentSettings` with 15 typed properties for type-safe access
- **Helper function** - `cookie_consent_settings()` for convenient access in views and code
- **Settings migration** - All defaults match previous config file values
- **Publishable migrations** - `--tag=cookie-consent-settings-migrations`

### Available Settings

| Property | Type | Default |
|----------|------|---------|
| `css_url` | `string` | CDN cookieconsent CSS |
| `js_url` | `string` | CDN cookieconsent JS |
| `content_href` | `?string` | `null` |
| `content_close` | `string` | `&#x274c;` |
| `popup_background` | `string` | `#696969` |
| `popup_text` | `string` | `#FFFFFF` |
| `popup_link` | `string` | `#FFFFFF` |
| `button_background` | `string` | `transparent` |
| `button_border` | `string` | `#f8e71c` |
| `button_text` | `string` | `#f8e71c` |
| `highlight_background` | `string` | `#f8e71c` |
| `highlight_border` | `string` | `#f8e71c` |
| `highlight_text` | `string` | `#000000` |
| `position` | `string` | `bottom-left` |
| `theme` | `string` | `block` |

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/compare/v1.1.1...v2.0.0

## v1.2.0 - 2025-12-27

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/1
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/2
* Configure Renovate by @renovate[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/3
* Delete .github/FUNDING.yml by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/5
* Delete renovate.json by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/6
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/7
* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/8
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/9
* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/10

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/1
* @renovate[bot] made their first contribution in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/3
* @jeffersongoncalves made their first contribution in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/5

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/compare/v1.1.1...v1.2.0

## v1.1.1 - 2025-03-03

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/compare/v1.1.0...v1.1.1

## v1.1.0 - 2025-03-02

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/compare/v1.0.1...v1.1.0

## v1.0.1 - 2025-03-01

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/compare/v1.0.0...v1.0.1

## v1.0.0 - 2025-03-01

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/commits/v1.0.0
