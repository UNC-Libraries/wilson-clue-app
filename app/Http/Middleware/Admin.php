<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin {

    public function handle($request, Closure $next, $guard = 'admin')
    {

        if ( !Auth::guard($guard)->check() || !Auth::guard($guard)->user()->admin )
        {
            dd(Auth::user());
            return redirect('/');
        }

        return $next($request);

    }

}
