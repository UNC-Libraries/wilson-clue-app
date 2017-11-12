<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Suspect;
use App\Game;
use App\Team;
use App\Agent;
use Illuminate\Support\Facades\DB;

class WebController extends Controller
{

    public function __construct()
    {

    }

    /**
     * Show the homepage
     *
     * @return welcome view
     */
    public function index()
    {

        $suspects = Suspect::get();

        $game = Game::active()->where('registration','=','1')->orderBy('start_time','desc')->first();

        $agents['active'] = Agent::active()->get();
        $agents['retired'] = Agent::retired()->get();

        $games = Game::archived()->get();
        $globals = DB::table('globals')->where('key','homepage')->first();
        $homepageAlert = $globals ? $globals->message : '';

        if($game){
            $globals = DB::table('globals')->where('key','special_notice')->first();
            $special_notice = $globals ? str_replace('||game_date||',$game->start_time->format('l, F jS'),
                str_replace('||game_time||',$game->start_time->format('g:i A'), $globals->message))
                : '';
        }


        return view('web.welcome', compact('suspects','game','agents','games','homepageAlert','special_notice'));
    }

    /**
     * Show a game archive
     *
     * @param  Request  $request
     * @param  string  $id
     * @return archive view
     */
    public function archive(Request $request, $id)
    {
        $game = Game::with('registeredTeams','registeredTeams.players')->findOrFail($id);
        $first_place = $game->registeredTeams->sortByDesc('score')->first();
        $second_place = $game->registeredTeams->sortByDesc('score')->slice(1,1)->first();
        $third_place = $game->registeredTeams->sortByDesc('score')->slice(2,1)->first();

        return view('web.archive',compact('game','first_place','second_place','third_place'));
    }

    /**
     * Show a login form
     *
     * @param  Request  $request
     * @param  string  $id
     * @return login view
     */
    public function login()
    {
        return view('auth.login');
    }
}