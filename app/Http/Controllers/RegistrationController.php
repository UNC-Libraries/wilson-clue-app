<?php

namespace App\Http\Controllers;

use App\Game;
use App\Mail\Registered;
use App\Player;
use App\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mail;

class RegistrationController extends Controller
{
    /**
     * Show the enlistment form
     */
    public function index(Request $request)
    {
        $game = Game::findOrFail($request->session()->get('gameId'));
        if ($game->registration) {
            return view('web.registration.enlist', compact('game'));
        } else {
            return redirect('/');
        }
    }

    /**
     * Show the team management form
     */
    public function teamManagement(Request $request)
    {
        $game = Game::find($request->session()->get('gameId'));
        $user = Auth::guard('player')->user();
        $team = $user->teams()->with('players')->active()->first();

        if (empty($team)) {
            return redirect()->route('player.logout');
        }

        if (! $team->waitlist) {
            $status_message_key = 'team_status_message:_registered_team';
        } else {
            if ($team->players->count() < $team::MINIMUM_PLAYERS && $game->spots_left > 0) {
                $status_message_key = 'team_status_message:_not_enough_players,_open_spots';
            } elseif ($team->players->count() < $team::MINIMUM_PLAYERS && $game->spots_left <= 0) {
                $status_message_key = 'team_status_message:_not_enough_players,_game_full';
            } else {
                $status_message_key = 'team_status_message:_waitlist';
            }
        }

        $status_message = DB::table('globals')->where('key', '=', $status_message_key)->first();
        $status_message = $status_message ? $status_message->message : '';

        $canRemove = $team->waitlist || $team->players->count() > $team::MINIMUM_PLAYERS ? true : false;

        return view('web.registration.team_management', compact('user', 'team', 'game', 'canRemove', 'status_message'));
    }

    public function updateTeam(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $user = Auth::guard('player')->user();
        $team = $user->teams()->with('players')->active()->first();

        $team->fill($request->all());
        $team->save();

        return redirect()->route('enlist.teamManagement');
    }

    public function addPlayer(Request $request)
    {
        $this->validate($request, [
            'onyen' => 'required',
        ]);

        $onyen = $request->get('onyen');
        $game = Game::with('registeredTeams')->find($request->session()->get('gameId'));
        $user = Auth::guard('player')->user();
        $team = $user->teams()->with('players')->active()->first();

        // Is the team at max capacity?
        if ($team->players->count() >= 5) {
            return redirect()->back()->withErrors('enlist.add_player.full')->withInput();
        }

        // Does the player already have an account?
        $match = Player::where('onyen', '=', $onyen)->first();
        $player = $match ? $match : new Player(['onyen' => $onyen]);
        // Update the player using the provided onyen
        $player->updateFromOnyen($onyen);
        $warnings = $player->getWarnings($game);

        if (! empty($warnings)) {
            return redirect()->back()->withErrors($warnings)->withInput();
        }

        // All is good!
        $player->save();
        $team->players()->attach($player);

        // Register team if a spot is available and email them
        if ($team->players->count() + 1 >= $team::MINIMUM_PLAYERS && $game->spots_left > 0) {
            $team->waitlist = false;
            $this->emailTeam($team->id, 'email:_fully_registered');
        }
        $team->save();

        return redirect()->route('enlist.teamManagement');
    }

    public function removePlayer(Request $request, $playerId)
    {
        $player = Player::findOrFail($playerId);
        $user = Auth::guard('player')->user();
        $team = $user->teams()->active()->first();
        $team->players()->detach($player);
        if ($team->players()->count() < $team::MINIMUM_PLAYERS) {
            $team->waitlist = true;
        }
        $team->save();

        return redirect()->route('enlist.teamManagement');
    }

    /**
     * Enlist a team
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function enlist(Request $request)
    {

        // Validate Form
        $this->validate($request, [
            'onyen' => 'required',
            'teamName' => 'required',
        ]);
        $onyen = $request->get('onyen');
        $teamName = $request->get('teamName');

        // Get the active game
        $game = Game::active()->with('registeredTeams')->where('registration', '=', '1')->orderBy('start_time', 'desc')->first();

        if (empty($game) || ! $game->registration) {
            return redirect()->back()->withErrors(['enlist.no_game'])->withInput();
        }

        // Does the player already have an account?
        $match = Player::where('onyen', '=', $onyen)->first();
        $player = $match ? $match : new Player(['onyen' => $onyen]);
        // Update the player using the provided onyen
        $player->updateFromOnyen($onyen);
        $warnings = $player->getWarnings($game);

        if (! empty($warnings)) {
            return redirect()->back()->withErrors($warnings)->withInput();
        }

        $player->save();

        // Create a team
        $team = new Team();
        $team->name = $teamName;
        $team->game()->associate($game);
        $team->save();
        $team->players()->attach($player);
        $team->save();

        // Send the email message
        $this->emailTeam($team->id, 'email:_initial_registration');

        Auth::guard('player')->login($player);

        return redirect()->route('enlist.teamManagement')->with('status', 'newTeam');
    }

    public function emailTeam($id, $email_text_key)
    {
        $team = Team::with([
            'players',
            'game',
        ])->findOrFail($id);

        $email_text = DB::table('globals')->where('key', '=', $email_text_key)->first();
        if (empty($email_text)) {
            return;
        }

        Mail::to($team->players)->send(new Registered($team, $email_text));
    }
}
