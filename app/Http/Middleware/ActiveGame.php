<?php

namespace App\Http\Middleware;

use Closure;
use App\Game;

class ActiveGame
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->session()->has('gameId')){
            $game = Game::active()->first();
            if(empty($game)){
                return redirect()->route('player.logout');
            } else {
                $request->session()->put('gameId',$game->id);
            }
        }
        return $next($request);
    }
}
