<?php

return [

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => env('STORAGE_PATH', storage_path()).'/app',
            'throw' => false,
            'serve' => true,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public/uploads'),
            'url' => env('APP_URL').'/storage',
            'throw' => false,
            'visibility' => 'public',
            'report' => false,
        ],
    ],

];
