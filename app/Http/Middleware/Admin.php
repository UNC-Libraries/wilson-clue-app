<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next, $guard = 'admin'): Response
    {
        if (! Auth::guard($guard)->check() || ! Auth::guard($guard)->user()->admin) {
            return redirect('/');
        }

        return $next($request);
    }
}
