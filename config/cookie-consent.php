<?php

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
