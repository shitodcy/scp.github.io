<?php

return [

   

    'default' => env('FILESYSTEM_DISK', 'local'),

   

         // config/filesystems.php
        'disks' => [
         // ... disk lain ...
        'backups' => [
        'driver' => 'local',
        'root' => storage_path('app/backups'), // Simpan backup di dalam storage/app/backups
    ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

   

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
