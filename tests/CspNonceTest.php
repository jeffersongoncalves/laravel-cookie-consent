<?php

use Illuminate\Support\Facades\Vite;

it('stamps the CSP nonce on every script tag', function () {
    Vite::useCspNonce('test-nonce');

    $html = view('cookie-consent::cookie-consent-body')->render();

    preg_match_all('/<script\b[^>]*>/', $html, $tags);

    expect($tags[0])->toHaveCount(2)->each->toContain('nonce="test-nonce"');
});

it('renders no nonce attribute when the app uses none', function () {
    $html = view('cookie-consent::cookie-consent-body')->render();

    expect($html)->toContain('<script')->not->toContain('nonce=');
});
