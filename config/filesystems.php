<?php

return [

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => env('STORAGE_PATH', storage_path()).'/app',
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
    ],

];
