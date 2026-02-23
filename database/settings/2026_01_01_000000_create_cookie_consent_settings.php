<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('cookie_consent', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('css_url', 'https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css');
            $blueprint->add('js_url', 'https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.js');
            $blueprint->add('content_header', 'Cookies used on the website!');
            $blueprint->add('content_message', 'This website uses cookies to ensure you get the best experience on our website.');
            $blueprint->add('content_dismiss', 'Got it!');
            $blueprint->add('content_allow', 'Allow cookies');
            $blueprint->add('content_deny', 'Decline');
            $blueprint->add('content_link', 'Learn more');
            $blueprint->add('content_href', null);
            $blueprint->add('content_close', '&#x274c;');
            $blueprint->add('content_target', '_blank');
            $blueprint->add('content_policy', 'Cookie Policy');
            $blueprint->add('popup_background', '#696969');
            $blueprint->add('popup_text', '#FFFFFF');
            $blueprint->add('popup_link', '#FFFFFF');
            $blueprint->add('button_background', 'transparent');
            $blueprint->add('button_border', '#f8e71c');
            $blueprint->add('button_text', '#f8e71c');
            $blueprint->add('highlight_background', '#f8e71c');
            $blueprint->add('highlight_border', '#f8e71c');
            $blueprint->add('highlight_text', '#000000');
            $blueprint->add('position', 'bottom-left');
            $blueprint->add('theme', 'block');
        });
    }
};
