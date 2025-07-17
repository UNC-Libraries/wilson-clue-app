<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;

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
