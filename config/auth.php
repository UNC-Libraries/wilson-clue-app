<?php

return [

    'guards' => [
        'player' => [
            'driver' => 'session',
            'provider' => 'player',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admin',
        ],
    ],

    'providers' => [
        'player' => [
            'driver' => env('APP_AUTH', 'ldap'),
            'model' => LdapRecord\Models\ActiveDirectory\User::class,
            'rules' => [],
            'database' => [
                'model' => App\Player::class,
                'sync_passwords' => true,
                'sync_attributes' => [
                    'onyen' => 'samaccountname',
                    'pid' => 'employeeid',
                    'first_name' => 'givenname',
                    'last_name' => 'sn',
                    'email' => 'mail',
                ],
                'sync_existing' => [
                    'onyen' => 'samaccountname',
                ],
            ],
        ],

        'admin' => [
            'driver' => env('APP_AUTH', 'ldap'),
            'model' => LdapRecord\Models\ActiveDirectory\User::class,
            'rules' => [],
            'database' => [
                'model' => App\Agent::class,
                'sync_passwords' => true,
                'sync_attributes' => [
                    'onyen' => 'samaccountname',
                    'pid' => 'employeeid',
                    'first_name' => 'givenname',
                    'last_name' => 'sn',
                    'email' => 'mail',
                ],
                'sync_existing' => [
                    'onyen' => 'samaccountname',
                ],
            ],
        ],
    ],

];
