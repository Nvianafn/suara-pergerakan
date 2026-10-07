<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'public_media_disk' => env('PUBLIC_MEDIA_DISK', 'public'),
    'disks' => [
        'local' => ['driver' => 'local', 'root' => storage_path('app/private'), 'throw' => true],
        'public' => ['driver' => 'local', 'root' => storage_path('app/public'), 'url' => env('APP_URL').'/storage', 'visibility' => 'public', 'throw' => true],
        'r2_public' => [
            'driver' => 's3', 'key' => env('R2_ACCESS_KEY_ID'), 'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => 'auto', 'bucket' => env('R2_PUBLIC_BUCKET', 'pmiisaintek-assets'),
            'endpoint' => env('R2_ENDPOINT'), 'url' => env('R2_PUBLIC_URL'),
            'use_path_style_endpoint' => true, 'throw' => true,
        ],
        'r2_private' => [
            'driver' => env('PRIVATE_MEDIA_DRIVER', 's3'),
            'root' => storage_path('app/private/media'),
            'key' => env('R2_ACCESS_KEY_ID'), 'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => 'auto', 'bucket' => env('R2_PRIVATE_BUCKET', 'pmiisaintek-private'),
            'endpoint' => env('R2_ENDPOINT'), 'use_path_style_endpoint' => true, 'throw' => true,
        ],
    ],
    'links' => [public_path('storage') => storage_path('app/public')],
];
