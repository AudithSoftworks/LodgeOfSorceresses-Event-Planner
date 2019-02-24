<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Supported: "local", "s3", "rackspace"
    |
    */
    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */
    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'chunks_expire_in' => 604800,
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],
        'cloudinary' => [
            'driver' => 'cloudinary',
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'key' => env('CLOUDINARY_API_KEY'),
            'secret' => env('CLOUDINARY_API_SECRET')
        ],
    ],
    'allowed_extensions' => [
        'jpeg', 'jpg', 'png', 'gif'
    ],
    'allowed_mimetypes' => [
        'image/jpeg',
        'image/png',
        'image/gif'
    ],
    'allowed_tags_and_limits' => [
        'avatar' => 1,
        'parse-screenshot' => 0, // 0 means no limit.
        'superstar-screenshot' => 0,
    ],
    'load_balancing' => [
        'enabled' => true,
        'length' => 2,
        'depth' => 2
    ]
];
