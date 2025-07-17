<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo(RouteServiceProvider::HOME);

        $middleware->throttleApi();

        $middleware->alias([
            'activeGame' => \App\Http\Middleware\ActiveGame::class,
            'admin' => \App\Http\Middleware\Admin::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'inProgressGame' => \App\Http\Middleware\InProgressGame::class,
            'player' => \App\Http\Middleware\Player::class,
            'validTeam' => \App\Http\Middleware\ValidTeam::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
