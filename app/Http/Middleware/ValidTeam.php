<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidTeam
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('teamId')) {
            $user = Auth::guard('player')->user();
            $team = $user->teams()->active()->first();
            if (empty($team)) {
                return redirect()->route('player.logout');
            } else {
                $request->session()->put('teamId', $team->id);
            }
        }

        return $next($request);
    }
}
