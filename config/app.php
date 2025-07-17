<?php

use Illuminate\Support\Facades\Facade;

return [

    'timezone' => 'America/New_York',

    'aliases' => Facade::defaultAliases()->merge([
        'ClueValidator' => App\Validation\ClueValidator::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

];
