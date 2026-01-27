<?php

use Botble\Shortcode\View\View;
use Botble\Theme\Theme;

return [

    /*
    |--------------------------------------------------------------------------
    | Inherit from another theme
    |--------------------------------------------------------------------------
    */

    'inherit' => null,

    /*
    |--------------------------------------------------------------------------
    | Listener from events
    |--------------------------------------------------------------------------
    */

    'events' => [

        'before' => function ($theme): void {
            // Pre-render setup
        },

        'beforeRenderTheme' => function (Theme $theme): void {
            $version = get_cms_version();

            // CSS - CDN with SRI
            $theme->asset()->add(
                'bootstrap-css',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                [],
                [],
                version: '5.3.3'
            );
            $theme->asset()->add(
                'bootstrap-icons',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
                [],
                [],
                version: '1.11.3'
            );

            // CSS - Theme
            $theme->asset()->usePath()->add('main-style', 'css/main.css', [], [], version: $version);

            // JS - Footer CDN
            $theme->asset()->container('footer')->add(
                'jquery',
                'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js'
            );

            if (function_exists('shortcode')) {
                $theme->composer(['page'], function (View $view) {
                    $view->withShortcodes();
                });
            }
        },

        'beforeRenderLayout' => [
            'default' => function ($theme): void {
                // Layout-specific assets
            },
        ],
    ],
];
