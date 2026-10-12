<?php

namespace App\Http\Controllers\Admin;

use App\Game;
use App\Http\Controllers\Controller;
use App\Location;
use App\MinigameImage;
use App\Quest;
use App\Question;
use App\Suspect;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuestController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @param $gameId
     * @param $questId
     * @return Factory|View
     */
    public function edit($gameId, $questId): Factory|View
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

        $questions = Question::where('location_id', '=', $quest->location_id)
            ->whereNotIn('id', $quest->questions->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get();

        $attachedMinigameImages = $quest->minigameImages ? $quest->minigameImages->pluck('id')->all() : [];
        $minigameImages = MinigameImage::whereNotIn('id', $attachedMinigameImages)
            ->get();

        return view('quest.edit', compact('game', 'quest', 'suspects', 'questions', 'games', 'minigameImages', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $gameId
     * @param $questId
     * @return RedirectResponse
     */
    public function update(Request $request, $gameId, $questId): RedirectResponse
    {
        $quest = Quest::with('location')->findOrFail($questId);
        $game = Game::findOrFail($gameId);

        // Unset the suspect or location from the game solution if they were used.
        if ($quest->location_id != $request->location_id) {
            if ($game->location_id == $quest->location_id) {
                $game->location_id = 0;
                $game->save();
            }
        }
        if ($quest->suspect_id != $request->suspect_id) {
            if ($game->suspect_id == $quest->suspect_id) {
                $game->suspect_id = 0;
                $game->save();
            }
        }

        $quest->fill($request->all());
        $quest->questions()->detach();
        $quest->minigameImages()->detach();

        switch ($quest->type) {
            case 'question':
                $attachArray = [];
                foreach (explode(',', $request->input('question_list')) as $order => $id) {
                    if ($id) {
                        $attachArray[$id] = ['order' => $order];
                    }
                }
                $quest->questions()->attach($attachArray);
                break;
            case 'minigame':
                //  explode(',', '') always returns [''] — never an empty array.
                // So when minigame_image_list is an empty string,
                // Clue tries to insert minigame_image_id = '' which MySQL rejects with an integer constraint error.
                // array_filter removes empty strings produced by explode(',', '')
                // on minigame_image_id. The question case above uses the same guard.
                $ids = array_filter(explode(',', $request->input('minigame_image_list')));
                $quest->minigameImages()->attach($ids);
                break;
        }

        $quest->save();

        return redirect()->route('admin.game.edit', $gameId)->with('alert', ['message' => $quest->location->name.' updated', 'type' => 'success']);
    }

}
