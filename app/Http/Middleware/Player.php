<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;

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
