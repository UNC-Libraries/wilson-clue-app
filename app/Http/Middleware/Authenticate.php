<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                switch ($guard) {
                    case 'player':
                        return redirect()->guest('go');
                    case 'admin':
                        return redirect()->guest('login');
                    default:
                        return redirect()->guest('/');
                }
            }
        }

        return $next($request);
    }
}
