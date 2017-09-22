<?php

namespace App\Http\Controllers\Admin;

use App\Evidence;
use App\IncorrectAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Game;
use App\Location;
use App\Suspect;
use App\Team;
use App\Player;
use App\Quest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GladosController extends Controller
{

    public function __construct()
    {

    }

    /**
     * Get the current viewing stats for a game
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function viewing($id)
    {
        $viewing = collect(DB::table('viewing')
                    ->where('game_id',$id)
                    ->where('updated_at','>',Carbon::now()->subMinutes(30))->get());

        $total = $viewing->count();
        $views = [];

        $grouped = $viewing->groupBy('route');
        foreach($grouped as $k => $v){
            if(preg_match('/ui\.quest/', $k)){
                list($route, $questId) = explode('--', $k);
                $quest = Quest::with('suspect', 'location')->find($questId);
                $views[] = [
                    'name' => $quest->suspect->name . ' ('.$quest->location->name.')',
                    'count' => $v->count(),
                    'percent' => floor($v->count() / $total * 100),
                ];
            } else {
                $views[] = [
                    'name' => ucfirst(str_replace('ui.', '',$k)),
                    'count' => $v->count(),
                    'percent' => floor($v->count() / $total * 100),
                ];
            }
        }

        return view('glados.viewing', compact('views','total'));
    }

    /**
     * Get the current status of all teams
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function status($id)
    {
        $teams = Team::where('game_id',$id)->registered()->get();
        $quests = Quest::where('game_id', $id)->with('completedBy','suspect')->get();

        return view('glados.status', compact('teams','quests'));
    }

    /**
     * Show questions that need to be judged
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function judgement($id)
    {
        $game = Game::with('quests')->findOrFail($id);
        return view('glados.judgement', compact('game'));
    }

    /**
     * Show the list of alerts for a game
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function alerts($id)
    {
        $game = Game::with('alerts')->findOrFail($id);
        return view('glados.alerts', compact('game'));
    }

}
