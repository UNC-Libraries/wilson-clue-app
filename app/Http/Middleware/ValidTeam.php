<?php

namespace App\Http\Middleware;

use App\Team;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidTeam
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $player = Auth::guard('player')->user();
        $gameId = $request->session()->get('gameId');
        $teamId = $request->session()->get('teamId');

        $team = Team::query()
            ->whereKey($teamId)
            ->where('game_id', $gameId)
            ->whereHas('players', function ($query) use ($player) {
                $query->whereKey($player->getAuthIdentifier());
            })
            ->whereNull('deleted_at')
            ->first();

        if (!$team) {
            $request->session()->forget('teamId');
            Auth::guard('player')->logout();

            return redirect()->route('player.login.form');
        }

        $request->attributes->set('currentTeam', $team);

        return $next($request);
    }
}
