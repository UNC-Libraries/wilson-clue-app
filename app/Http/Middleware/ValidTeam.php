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
     * Verify that the session team belongs to the authenticated player and game.
     * If no team has been selected yet, securely select the player's team for
     * the current game and persist only that validated team's ID.
     *
     * Invalid or stale team selections are cleared before the player is sent
     * back to the login page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $player = Auth::guard('player')->user();
        $gameId = $request->session()->get('gameId');
        $teamId = $request->session()->get('teamId');

        // Find the list of active teams in this game that belong to the
        // authenticated player. Session values only select from this set.
        // Likely only one team.
        $teamQuery = Team::query()
            ->where('game_id', $gameId)
            ->whereHas('players', function ($query) use ($player) {
                $query->whereKey($player->getAuthIdentifier());
            })
            ->whereNull('deleted_at');

        if ($request->session()->has('teamId')) {
            // Sets $team to the session-selected team if it belongs to the authenticated player and current game.
            $team = (clone $teamQuery)->whereKey($teamId)->first();
        } else {
            // $team is the player's first active team in this game, ordered by
            // ID; persist its ID so later requests can validate that selection.
            $team = $teamQuery->orderBy('id')->first();
            if ($team) {
                $request->session()->put('teamId', $team->id);
            }
        }

        if (!$team) {
            $request->session()->forget('teamId');
            Auth::guard('player')->logout();

            return redirect()->route('player.login.form');
        }

        $request->attributes->set('currentTeam', $team);

        return $next($request);
    }
}
