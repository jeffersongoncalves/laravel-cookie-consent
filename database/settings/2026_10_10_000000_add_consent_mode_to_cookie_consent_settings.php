<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('cookie_consent', function (SettingsBlueprint $blueprint): void {
            // "info" is what the banner did before this setting existed (cookieconsent's default).
            $blueprint->add('type', 'info');
            $blueprint->add('consent_mode', false);
        });
    }
};
