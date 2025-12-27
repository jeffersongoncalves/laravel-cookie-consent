<div class="filament-hidden">

![Laravel Created By](https://raw.githubusercontent.com/jeffersongoncalves/laravel-cookie-consent/master/art/jeffersongoncalves-laravel-cookie-consent.png)

</div>

# Laravel Cookie Consent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-cookie-consent.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-cookie-consent)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-cookie-consent/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-cookie-consent/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-cookie-consent.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-cookie-consent)

This Laravel package provides a simple and elegant way to implement cookie consent on your website, ensuring compliance with privacy regulations like GDPR and CCPA. It offers a clean and customizable interface, allowing you to easily manage and display cookie consent banners and preferences.

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-cookie-consent
```

## Usage

Publish config file.

```bash
php artisan vendor:publish --tag=cookie-consent-config
```

Publish views (optional).

```bash
php artisan vendor:publish --tag=cookie-consent-views
```

Publish translations (optional).

```bash
php artisan vendor:publish --tag=cookie-consent-translations
```

Add head template.

```php
@include('cookie-consent::cookie-consent-head')
```

Add body template.

```php
@include('cookie-consent::cookie-consent-body')
```

## Configuration

You can customize the appearance and behavior of the cookie consent banner by editing the `config/cookie-consent.php` file.

```php
return [
    'css' => 'https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css',
    'js' => 'https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js',
    'content' => [
        'href' => null,
        'close' => '&#x274c;',
    ],
    'palette' => [
        'popup' => [
            'background' => '#696969',
            'text' => '#FFFFFF',
            'link' => '#FFFFFF',
        ],
        'button' => [
            'background' => 'transparent',
            'border' => '#f8e71c',
            'text' => '#f8e71c',
        ],
        'highlight' => [
            'background' => '#f8e71c',
            'border' => '#f8e71c',
            'text' => '#000000',
        ],
    ],
    'position' => 'bottom-left', // top-left, top-right, bottom-left, bottom-right
    'theme' => 'block', // block, edgeless, classic
];
```

### Position

| Top Left | Top Right |
| :---: | :---: |
| ![Top Left](screenshots/cookie-consent-top-left.png) | ![Top Right](screenshots/cookie-consent-top-right.png) |
| **Bottom Left** | **Bottom Right** |
| ![Bottom Left](screenshots/cookie-consent-bottom-left.png) | ![Bottom Right](screenshots/cookie-consent-bottom-right.png) |

## Translations

This package supports multiple languages. The following languages are currently available:

- Arabic (`ar`)
- Czech (`cs`)
- German (`de`)
- English (`en`)
- Spanish (`es`)
- Persian (`fa`)
- French (`fr`)
- Hebrew (`he`)
- Indonesian (`id`)
- Italian (`it`)
- Japanese (`ja`)
- Dutch (`nl`)
- Polish (`pl`)
- Portuguese (`pt`)
- Portuguese (Brazil) (`pt_BR`)
- Portuguese (Portugal) (`pt_PT`)
- Slovak (`sk`)
- Turkish (`tr`)

If you want to customize the translations, you can publish the language files:

```bash
php artisan vendor:publish --tag=cookie-consent-translations
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

This package uses the [Osano CookieConsent](https://github.com/osano/cookieconsent) plugin.

- [Jèfferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
