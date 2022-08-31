<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Player;
use App\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$teams = Team::with('teams.players','game')->where('game_id','=',$id)->firstOrFail();
        //return view('game.teams',['teams'=>$teams]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $team = Team::with('game', 'players')->findOrFail($id);
        $game = $team->game;
        $dummy = new Player;
        $academic_group_options = $dummy::ACADEMIC_GROUP_OPTIONS;
        $class_options = $dummy::CLASS_OPTIONS;

        return view('team.edit', compact('team', 'game', 'academic_group_options', 'class_options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $team = Team::findOrFail($id);

        foreach ($team->getAttributes() as $key => $value) {
            if (isset($request->{$key}) && $value !== $request->{$key}) {
                $team->{$key} = $request->{$key};
            }
        }

        $team->save();

        return redirect()->back()->with('alert', ['message' => 'Changes Saved!', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $team = Team::findOrFail($id);
        $team->foundDna()->detach();
        $team->completedQuests()->detach();
        $team->correctQuestions()->detach();
        $team->players()->detach();
        $team->delete();

        return redirect()->route('admin.game.teams', ['id' => $team->game->id])->with('alert', ['message' => $team->name.'  deleted!', 'type' => 'warning']);
    }

    /**
     * Toggle team's waitlist
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleWaitlist($id)
    {
        $team = Team::findOrFail($id);
        $team->waitlist = ! $team->waitlist;
        $team->save();

        $type = $team->waitlist ? 'danger' : 'success';
        $message = $team->waitlist ? $team->name.' moved to the waitlist' : $team->name.' registered';

        return redirect()->back()->with('alert', ['type' => $type, 'message' => $message]);
    }

    /**
     * Add Player to team
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addPlayer(Request $request, $id)
    {
        $team = Team::with('game', 'players')->findOrFail($id);

        if ($team->players->count() == 5) {
            redirect()->back()->with('alert', ['type' => 'danger', 'message' => 'Team has 5 players']);
        }

        $onyen = $request->get('onyen');
        $override = $request->get('override_non_student') ? true : false;

        if ($onyen) {

            // Does the player already have an account?
            $match = Player::where('onyen', '=', $onyen)->first();
            $player = $match ? $match : new Player();
            // Update the player using the provided onyen
            $player->updateFromOnyen($onyen, $override);
            $warnings = $player->getWarnings($team->game);
            array_walk($warnings, function (&$v, $k) {
                $v = trans($v);
            });
            if (! empty($warnings)) {
                return redirect()->back()->withErrors($warnings)->withInput();
            }
        } else {
            $this->validate($request, [
                'email' => 'required | email',
                'password' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'class_code' => 'required',
                'academic_group_code' => 'required',
            ]);

            $player = new Player();
            $player->fill($request->all());
            $player->onyen = $request->get('email');
            $player->password = Hash::make($request->get('password'));
            $player->manual = true;
        }

        $player->save();

        $team->players()->attach($player);
        $team->save();

        return redirect()->back();
    }

    /**
     * Remove Player from team
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removePlayer($id, $playerId)
    {
        $team = Team::findOrFail($id);
        $team->players()->detach($playerId);
        $team->save();

        return redirect()->back();
    }
}
