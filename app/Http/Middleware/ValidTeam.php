<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;

class ValidTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
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
