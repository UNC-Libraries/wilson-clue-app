<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Player {

    public function handle($request, Closure $next, $guard = 'player')
    {

        if ( !Auth::guard($guard)->check())
        {
            return redirect('/');
        }

        return $next($request);

    }

}
