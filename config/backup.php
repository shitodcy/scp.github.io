<?php

return [
    'backup' => [
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [
            'files' => [
               
                'include' => [
                    base_path(),
                ],

               
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                'follow_links' => false,

                'ignore_unreadable_directories' => false,

                'relative_path' => null,
            ],

            
            'databases' => [
                'mysql',
            ],
        ],

        
        'temporary_directory' => storage_path('app/backup-temp'),

        'destination' => [
            
            'filename_prefix' => '',

           
            'disks' => [
                'backups', 
            ],
        ],
    ],

   
    'notifications' => [
        // ...
    ],

    
    'actions' => [
        // ...
    ],

    
    'cleanup' => [
        // ...
    ],

    'monitor_backups' => [
        // ...
    ],

];
