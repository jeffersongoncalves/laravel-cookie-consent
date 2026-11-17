<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('cookie_consent', function (SettingsBlueprint $blueprint): void {
            // All off by default: upgrading changes nothing until you opt in.
            $blueprint->add('mode', 'banner');
            $blueprint->add('block_scripts', false);
            $blueprint->add('script_categories', []);
            $blueprint->add('honor_gpc', false);
            $blueprint->add('preferences_css_url', 'https://cdn.jsdelivr.net/npm/vanilla-cookieconsent@3.1.0/dist/cookieconsent.css');
            $blueprint->add('preferences_js_url', 'https://cdn.jsdelivr.net/npm/vanilla-cookieconsent@3.1.0/dist/cookieconsent.umd.js');
        });
    }
};
