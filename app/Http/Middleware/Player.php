<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Player
{
    public function handle(Request $request, Closure $next, $guard = 'player'): Response
    {
        if (! Auth::guard($guard)->check()) {
            return redirect('/');
        }

        return $next($request);
    }
}
