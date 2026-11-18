# Changelog

All notable changes to this project will be documented in this file.

## 4.6.0 - 2026-11-17

### Block scripts until consent

`block_scripts` holds the scripts of the analytics and chat packages (`type="text/plain" data-category="…"`) until the visitor allows their category, then runs them with their CSP nonce, in page order (a library loads before its init script).

| Category | Packages |
|---|---|
| analytics | gtag, clarity, hotjar, mixpanel, amplitude, matomo |
| marketing | gtm, pixel |
| functionality | crisp, tawk-to |
| never blocked (no cookies) | plausible, fathom, simple-analytics, pirsch, cloudflare-web-analytics, goatcounter, umami |

Google tags are left to Consent Mode when `consent_mode` is on; `script_categories` overrides a category. The packages need their next minor (they ask for the attributes through `cookie_consent_script_attributes()` behind `function_exists()`).

### A choice per category

`mode = categories` shows a preferences banner (vanilla-cookieconsent 3.1.0): Accept all, Reject all and a toggle per category in use. Consent Mode follows each category (analytics_storage / ad signals) and the privacy ledgers get one consent per category (`cookies_analytics`, `cookies_marketing`, `cookies_functionality`; CCPA: refusing marketing is the opt-out).

### Global Privacy Control

`honor_gpc` refuses the marketing category and the Consent Mode ad signals on a GPC signal.

### Cookie policy

`@include('cookie-consent::policy')` renders the cookie table of your cookie policy from the packages installed, grouped by category, translated, lifetimes in the app locale; add your own rows with `cookies`.

Every new setting is off: run `php artisan migrate` after updating. 24 locales for the new texts.

## 4.5.0 - 2026-11-17

### Banner choices in the GDPR and CCPA suites

The banner already recorded allow and deny in laravel-lgpd-consent (4.4.0). It now does the same for:

- **laravel-gdpr-consent** (`gdpr-consent.route.enabled`): consent under the purpose `cookies`, as proof of consent (GDPR Art. 7(1)).
- **laravel-ccpa-opt-out** (`ccpa-opt-out.route.enabled`): deny opts out of the sale or sharing of personal information (`sale_sharing`), allow opts back in.

With several installed, each choice goes to all of them, each with its own pending slot. Nothing changes unless one of these packages is installed with its endpoint enabled.

### Fix

- A failed choice waiting for its retry no longer gets resent after a newer choice was recorded, so the ledger always ends on the latest choice.

## 4.4.0 - 2026-11-16

### LGPD consent ledger integration

When [laravel-lgpd-consent](https://github.com/jeffersongoncalves/laravel-lgpd-consent) is installed with its endpoint enabled, the banner records every **allow/deny** in the consent ledger under the purpose `cookies` (proof of consent, LGPD art. 8). Dismiss is ignored.

- Detected automatically via the `lgpd-consent.store` route: no new dependency or setting
- `keepalive` requests, `XSRF-TOKEN` cookie first (works on cached pages)
- Latest choice kept pending until confirmed: ordered delivery, retries, resent on next page load
- Works alongside Google Consent Mode

No changes when laravel-lgpd-consent is not installed.

## 4.3.0 - 2026-11-02

### Translatable banner text

Closes #32 (#33).

- Banner text now ships translated in 24 locales (`resources/lang/{locale}/banner.php`): `ar az cs de en es fa fr he hi id it ja nl pl pt pt_BR pt_PT ru sk tr uk uz zh_CN`. Publish with `php artisan vendor:publish --tag=cookie-consent-translations`.
- `content_header`, `content_message`, `content_dismiss`, `content_allow`, `content_deny`, `content_link` and `content_policy` are now nullable. Null renders `__('cookie-consent::banner.<key>')` in the current locale; a string overrides it.
- The "learn more" link is hidden when `content_href` is null.
- Buttons no longer carry cookieconsent's hard-coded English `aria-label`s, so screen readers announce the translated text.

### Upgrading

Run `php artisan migrate`. The new settings migration sets the text settings that still hold the old English defaults to null, so they start translating; customised text is kept. The settings cache entry is refreshed by the migration.

If you use **filament-cookie-consent**, its settings page still marks these fields as required, so an update there is needed before admins can save with empty (translated) fields.

## 4.2.0 - 2026-10-10

### Added

- **Google Consent Mode v2.** New `consent_mode` setting: the head view pushes `gtag('consent', 'default', …)` for `ad_storage`, `ad_user_data`, `ad_personalization` and `analytics_storage` (restoring a previous choice from the `cookieconsent_status` cookie), and the banner pushes `gtag('consent', 'update', …)` when the visitor chooses. Works with [laravel-gtm](https://github.com/jeffersongoncalves/laravel-gtm), [laravel-gtag](https://github.com/jeffersongoncalves/laravel-gtag) or any Google tag — include the cookie consent head view **before** them.
- **Compliance `type`** setting: `info` (default), `opt-in` or `opt-out`. The package never passed a type before, so the banner always ran as a notice and the Allow/Decline texts were never shown.

| type | before a choice | allow | deny |
|---|---|---|---|
| opt-in | denied | granted | denied |
| opt-out | granted | granted | denied |
| info | granted | — | — |

### Upgrade

Run `php artisan migrate` (a new settings migration adds the two settings). Defaults keep the previous behaviour until you turn `consent_mode` on.

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/compare/v4.1.0...4.2.0

## v4.1.0 - 2026-10-09

- Every `<script>` rendered by the package carries Laravel's Vite CSP nonce when the app sets one (e.g. via laravel-security-headers), so nonce-based `script-src` policies work without `'unsafe-inline'`.

## 4.0.1 - 2026-09-23

### Changed

- **Dependencies:** `spatie/laravel-settings` is now required as `^3.0`. The `^4.0` alternative pointed to a version that does not exist.

### What's Changed

* fix: pin GitHub Actions to commit SHA by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/16
* docs: add Buy Me a Coffee sponsor link by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/17
* chore: add GitHub Sponsors to FUNDING.yml by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/18
* ci: standardize update-changelog workflow (master) by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/19
* ci: standardize dependabot config by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/20
* ci: standardize tests workflow (master) by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/21
* chore(deps): drop non-existent spatie/laravel-settings ^4.0 by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/23
* ci: standardize tests workflow (master) by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/22

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/compare/v4.0.0...4.0.1

## v4.0.0 - 2026-08-01

### Security

Drop Laravel 11 support (EOL, unpatched). Require `laravel/framework: ^12.61.1|^13.12.0` to fix:

- GHSA-crmm-hgp2-wgrp — Temporary Signed URL Path Confusion
- GHSA-5vg9-5847-vvmq — CRLF injection in default email validation rule

**Breaking change**: Laravel 11 is no longer supported.

## v3.0.3 - 2026-04-26

### What's Changed

* Bump ramsey/composer-install from 3 to 4 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/12
* Bump dependabot/fetch-metadata from 2.5.0 to 3.0.0 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-cookie-consent/pull/13

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-cookie-consent/compare/v3.0.2...v3.0.3

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
