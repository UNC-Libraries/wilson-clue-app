<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Game;
use Closure;

class ActiveGame
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('gameId')) {
            $game = Game::active()->first();
            if (empty($game)) {
                return redirect()->route('player.logout');
            } else {
                $request->session()->put('gameId', $game->id);
            }
        }

        return $next($request);
    }
}
