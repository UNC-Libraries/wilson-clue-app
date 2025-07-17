<?php

return [

    'mailers' => [
        'mailgun' => [
            'transport' => 'mailgun',
        ],
    ],

    'markdown' => [
        'theme' => 'none',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
