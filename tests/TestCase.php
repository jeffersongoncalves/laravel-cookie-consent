<?php

namespace JeffersonGoncalves\CookieConsent\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\CookieConsent\CookieConsentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->runSettingsMigration();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelSettingsServiceProvider::class,
            CookieConsentServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', $this->testing_connection());
    }

    protected function setUpDatabase(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group');
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->json('payload');
            $table->timestamps();
            $table->unique(['group', 'name']);
        });
    }

    protected function runSettingsMigration(): void
    {
        /** @var SettingsMigration $migration */
        $migration = require __DIR__.'/../database/settings/2026_01_01_000000_create_cookie_consent_settings.php';
        $migration->up();
    }

    /**
     * The original in-memory SQLite connection by default; CI (tests.yml) sets
     * COOKIE_CONSENT_TEST_DB_* to run the same suite on MySQL and PostgreSQL. Not DB_CONNECTION:
     * Testbench pins it to "testing", which would always win over a driver read from it.
     *
     * @return array<string, mixed>
     */
    protected function testing_connection(): array
    {
        $driver = env('COOKIE_CONSENT_TEST_DB_DRIVER', 'sqlite');

        if ($driver === 'sqlite') {
            return [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ];
        }

        return [
            'driver' => $driver,
            'host' => env('COOKIE_CONSENT_TEST_DB_HOST', '127.0.0.1'),
            'port' => env('COOKIE_CONSENT_TEST_DB_PORT'),
            'database' => env('COOKIE_CONSENT_TEST_DB_DATABASE', 'testing'),
            'username' => env('COOKIE_CONSENT_TEST_DB_USERNAME', 'root'),
            'password' => env('COOKIE_CONSENT_TEST_DB_PASSWORD', ''),
            'charset' => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
            'prefix' => '',
        ];
    }
}
