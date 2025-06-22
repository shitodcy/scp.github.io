<?php

return [
    'backup' => [
        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [
            'files' => [
                /*
                 * The list of directories and files that will be backed up.
                 */
                'include' => [
                    base_path(),
                ],

                /*
                 * These directories and files will be excluded from the backup.
                 *
                 * Dumping big files might be slow or exhaust memory.
                 */
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                'follow_links' => false,

                'ignore_unreadable_directories' => false,

                'relative_path' => null,
            ],

            /*
             * The names of the database connections to be backed up.
             *
             * To back up all connections, set this value to `['*']`.
             */
            'databases' => [
                'mysql',
            ],
        ],

        /*
         * The path to the temporary directory where the backup will be created.
         */
        'temporary_directory' => storage_path('app/backup-temp'),

        'destination' => [
            /*
             * The filename prefix.
             *
             * The date and time will be appended automatically.
             */
            'filename_prefix' => '',

            /*
             * The disk names on which the backups will be stored.
             */
            // --- INI BAGIAN YANG PALING PENTING UNTUK DIUBAH ---
            'disks' => [
                'backups', // <-- Ubah dari ['local'] menjadi ['backups']
            ],
        ],
    ],

    /*
     * You can get notified when specific events occur.
     */
    'notifications' => [
        // ...
    ],

    /*
     * Here you can specify the actions that should be executed before and
     * after the backup is created.
     */
    'actions' => [
        // ...
    ],

    /*
     * Here you can specify which backups should be cleaned up.
     */
    'cleanup' => [
        // ...
    ],

    'monitor_backups' => [
        // ...
    ],

];
