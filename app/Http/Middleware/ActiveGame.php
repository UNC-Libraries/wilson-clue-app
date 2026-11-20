<?php

namespace App\Http\Middleware;

use App\Game;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActiveGame
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('gameId')) {
            $game = Game::active()->first();
            if (empty($game)) {
                Auth::guard('player')->logout();

                return redirect()->route('player.login.form');
            } else {
                $request->session()->put('gameId', $game->id);
            }
        }

        return $next($request);
    }
}
