# Laravel Cookie Consent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-cookieconsent.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-cookieconsent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-cookieconsent/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-cookieconsent/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-cookieconsent/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-cookieconsent/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-cookieconsent.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-cookieconsent)


This Laravel package automatically logs the currently logged-in user's ID to the `created_by`, `updated_by`, `deleted_by`, and `restored_by` fields of your Eloquent models. It also automatically timestamps the `restored_at` field when a model is restored. This simplifies the tracking of data modifications and provides valuable auditing capabilities. The package is easy to install and configure, seamlessly integrating with your existing Laravel application.

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-cookieconsent
```

## Usage

Add head template.

```bladehtml

```

Add body template.

```bladehtml

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

- [Jèfferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
