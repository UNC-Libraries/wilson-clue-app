<?php

namespace App\Http\Controllers\Admin;

use App\Game;
use App\Http\Controllers\Controller;
use App\Quest;
use App\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GladosController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Get the current viewing stats for a game
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewing($id)
    {
        $viewing = collect(DB::table('viewing')
                    ->where('game_id', $id)
                    ->where('updated_at', '>', Carbon::now()->subMinutes(30))->get());

        $total = $viewing->count();
        $views = [];

        $grouped = $viewing->groupBy('route');
        foreach ($grouped as $k => $v) {
            if (preg_match('/ui\.quest/', $k)) {
                [$route, $questId] = explode('--', $k);
                $quest = Quest::with('suspect', 'location')->find($questId);
                $views[] = [
                    'name' => $quest->suspect->name.' ('.$quest->location->name.')',
                    'count' => $v->count(),
                    'percent' => floor($v->count() / $total * 100),
                ];
            } else {
                $views[] = [
                    'name' => ucfirst(str_replace('ui.', '', $k)),
                    'count' => $v->count(),
                    'percent' => floor($v->count() / $total * 100),
                ];
            }
        }

        return view('glados.viewing', compact('views', 'total'));
    }

    /**
     * Get the current status of all teams
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function status($id)
    {
        $teams = Team::where('game_id', $id)->registered()->get();
        $quests = Quest::where('game_id', $id)->with('completedBy', 'suspect')->get();

        return view('glados.status', compact('teams', 'quests'));
    }
}
