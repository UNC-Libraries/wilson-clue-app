<?php

return [

    'files' => env('STORAGE_PATH', storage_path()).'/framework/sessions',
    'secure' => env('SESSION_SECURE_COOKIE', false),
    'http_only' => env('SESSION_HTTP_ONLY', true),
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

];
