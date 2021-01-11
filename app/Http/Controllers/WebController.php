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

        $game = Game::active()->orderBy('start_time','desc')->first();

        $agents['active'] = Agent::active()->get();
        $agents['retired'] = Agent::retired()->get();

        $games = Game::archived()->get();
        $globals = DB::table('globals')->where('key','homepage')->first();
        $homepageAlert = $globals ? $globals->message : '';
        $special_notice = null;
        $registration_closed = null;

        if($game){
            // get the special notice message
            $globals = DB::table('globals')->where('key','special_notice')->first();
            $special_notice = $globals ? str_replace('||game_date||',$game->start_time->format('l, F jS'),
                str_replace('||game_time||',$game->start_time->format('g:i A'), $globals->message))
                : '';
            // get the registration_closed message
            $globals = DB::table('globals')->where('key','registration_closed')->first();
            $registration_closed = $globals ? str_replace('||game_date||',$game->start_time->format('l, F jS'),
                str_replace('||game_time||',$game->start_time->format('g:i A'), $globals->message))
                : '';
        }


        return view('web.welcome', compact('suspects','game','agents','games','homepageAlert','special_notice','registration_closed'));
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
        $teams = $game->registeredTeams;

        $correct_indictments = $teams->filter(function ($team) {
            return $team->indictment_correct == true;
        })->sortByDesc('score');
        $incorrect_indictments = $teams->filter(function ($team) {
            return $team->indictment_correct == false;
        })->sortByDesc('score');

        $teams = $correct_indictments->merge($incorrect_indictments);

        $first_place = $teams->first();
        $second_place = $teams->slice(1,1)->first();
        $third_place = $teams->slice(2,1)->first();

        return view('web.archive',compact('game','teams','first_place','second_place','third_place'));
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
