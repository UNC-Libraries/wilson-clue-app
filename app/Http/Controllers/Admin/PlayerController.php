<?php

namespace App\Http\Controllers\Admin;

use App\Game;
use App\Http\Controllers\Controller;
use App\Player;
use Hash;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $games = Game::get();
        $sortOptions = [
            'last_name' => 'Last Name',
            'onyen' => 'Onyen',
            'team' => 'Team Count',
        ];
        $selectedSort = $request->get('sort_by') ? $request->get('sort_by') : 'last_name';
        $sortOrder = [
            'asc' => 'asc',
            'desc' => 'desc',
        ];
        $selectedSortOrder = $request->get('sort_order') ? $request->get('sort_order') : 'asc';

        $dummy = new Player();
        $academic_group_options = $dummy::ACADEMIC_GROUP_OPTIONS;
        $class_options = $dummy::CLASS_OPTIONS;

        //Request Vars
        $game = $request->get('game');
        $class = $request->get('class');
        if ($class) {
            array_walk($class, function (&$v) {
                $v = $v === null ? '' : $v;
            });
        }
        $group = $request->get('group');
        if ($group) {
            array_walk($group, function (&$v) {
                $v = $v === null ? '' : $v;
            });
        }
        $played = $request->get('played');
        $nonStudent = $request->get('non_student');
        $manual = $request->get('manual');
        $search = $request->get('search');

        $players = Player::select();

        if (! empty($game)) {
            $players->whereHas('teams', function ($query) use ($game) {
                $query->whereIn('game_id', $game);
            });
        }

        if (! empty($class)) {
            $players->whereIn('class_code', $class);
        }
        if (! empty($group)) {
            $players->whereIn('academic_group_code', $group);
        }
        if (! empty($played)) {
            switch ($played) {
                case 'yes':
                    $players->where('checked_in', 1);
                    break;
                case 'no':
                    $players->where('checked_in', 0);
                    break;
                default:
                    break;
            }
        }
        if (! empty($nonStudent)) {
            $players->where('student', 0);
        }
        if (! empty($manual)) {
            $players->where('manual', 1);
        }
        if (! empty($search)) {
            $qs = "%$search%";
            $players->where('first_name', 'like', $qs)
                ->orWhere('last_name', 'like', $qs)
                ->orWhere('pid', 'like', $qs)
                ->orWhere('email', 'like', $qs)
                ->orWhere('onyen', 'like', $qs);
        }

        if ($selectedSort == 'team') {
            $players = $players->with('teams')->get();
            if ($selectedSortOrder == 'desc') {
                $players = $players->sortByDesc(function ($player, $key) {
                    return $player->teams->count();
                });
            } else {
                $players = $players->sortBy(function ($player, $key) {
                    return $player->teams->count();
                });
            }
        } else {
            $players = $players->with('teams')->orderBy($selectedSort, $selectedSortOrder)->get();
        }

        return view('player.index', compact(
            'players',
            'games',
            'sortOptions',
            'selectedSort',
            'sortOrder',
            'selectedSortOrder',
            'class_options',
            'academic_group_options',
            'request',
            'search'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $player = Player::with('teams', 'teams.game')->findOrFail($id);

        return view('player.edit', compact('player'));
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
        $player = Player::findOrFail($id);
        $player->checked_in = false;
        $player->fill($request->all());
        if ($request->get('password')) {
            $player->onyen = $request->get('email');
            $player->password = Hash::make($request->get('password'));
        }
        $player->save();

        return redirect()->route('admin.player.index')->with('alert', ['type' => 'success', 'message' => $player->full_name.' updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $message = $player->full_name.' removed';
        $player->teams()->detach();
        $player->delete();

        return redirect()->route('admin.player.index')->with('alert', ['type' => 'success', 'message' => $message]);
    }
}
