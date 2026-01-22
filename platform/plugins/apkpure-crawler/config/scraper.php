<?php

return [
    'playwright' => [
        'host' => env('PLAYWRIGHT_HOST', 'localhost'),
        'port' => env('PLAYWRIGHT_PORT', 3000),
        'timeout' => env('PLAYWRIGHT_TIMEOUT', 30000),
    ],

    'storage' => [
        'disk' => env('SCRAPER_STORAGE_DISK', 'local'),
        'path' => 'scraped',
    ],

    'queue' => [
        'connection' => env('SCRAPER_QUEUE_CONNECTION', 'database'),
        'name' => 'scraper',
    ],

    'retry' => [
        'times' => 3,
        'delay' => 5000,
    ],

    'rate_limit' => [
        'enabled' => true,
        'max_per_minute' => 10,
    ],
];
