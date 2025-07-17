<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Game;
use Closure;

class InProgressGame
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('override_in_progress')) {
            $game = Game::findOrFail($request->session()->get('override_in_progress'));
        } else {
            $game = Game::active()->inProgress()->first();
        }

        if (empty($game)) {
            return redirect()->route('gameover');
        } else {
            if (! $request->session()->has('gameId')) {
                $request->session()->put('gameId', $game->id);
            }
        }

        return $next($request);
    }
}
