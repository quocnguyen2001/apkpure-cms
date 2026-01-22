<?php

return [
    'default_disk' => env('MEDIA_DISK', 'public'),

    'default_visibility' => env('MEDIA_VISIBILITY', 'public'),

    'validation' => [
        'max_size' => env('MEDIA_MAX_SIZE', 10240),
    ],

    'download' => [
        'timeout' => env('MEDIA_DOWNLOAD_TIMEOUT', 30),
        'max_retries' => env('MEDIA_DOWNLOAD_RETRIES', 3),
        'user_agent' => 'Mozilla/5.0 (compatible; MediaService/1.0)',
    ],
];
