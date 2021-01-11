<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use App\Quest;
use App\Game;
use App\Location;
use App\Suspect;
use App\Question;
use App\Evidence;
use App\MinigameImage;
use DB;

class QuestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
    public function edit($gameId,$questId)
    {
        $game = Game::findOrFail($gameId);
        $suspects = Suspect::get();
        $locations = Location::get();
        $games = Game::get();
        $quest = Quest::with([
            'location',
            'suspect',
            'questions',
            'minigameImages',
        ])->findOrFail($questId);

        $questions = Question::where('location_id','=',$quest->location_id)
                            ->whereNotIn('id',$quest->questions->pluck('id'))
                            ->orderBy('created_at','desc')
                            ->get();

        $attachedMinigameImages = $quest->minigameImages ? $quest->minigameImages->pluck('id')->all() : [];
        $minigameImages = MinigameImage::whereNotIn('id',$attachedMinigameImages)
                            ->get();

        return view('quest.edit',compact('game','quest','suspects','questions','games','minigameImages','locations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $gameId, $questId)
    {
        $quest = Quest::with('location')->findOrFail($questId);
        $game = Game::findOrFail($gameId);

        // Unset the suspect or location from the game solution if they were used.
        if($quest->location_id != $request->location_id){
            if($game->location_id == $quest->location_id){
                $game->location_id = 0;
                $game->save();
            }
        }
        if($quest->suspect_id != $request->suspect_id){
            if($game->suspect_id == $quest->suspect_id){
                $game->suspect_id = 0;
                $game->save();
            }
        }

        $quest->fill($request->all());
        $quest->questions()->detach();
        $quest->minigameImages()->detach();

        switch($quest->type)
        {
            case 'question':
                $attachArray = [];
                foreach(explode(',',$request->input('question_list')) as $order => $id)
                {
                    if($id){
                        $attachArray[$id] = ['order' => $order];
                    }
                }
                $quest->questions()->attach($attachArray);
                break;
            case 'minigame':
                $quest->minigameImages()->attach(explode(',',$request->input('minigame_image_list')));
                break;
        }

        $quest->save();
        return redirect()->route('admin.game.edit',$gameId)->with('alert',array('message' => $quest->location->name.' updated', 'type' => 'success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
