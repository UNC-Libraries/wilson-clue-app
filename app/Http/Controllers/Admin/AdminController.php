<?php

namespace App\Http\Controllers\Admin;

use App\Http\Middleware\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Game;
use App\Agent;
use Illuminate\Support\Facades\Auth;
use Adldap\Adldap;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(){
        $globals = DB::table('globals')->where('key','homepage')->first();
        $homepageAlert = $globals ? $globals->message : '';
        return view('admin.index',compact('homepageAlert'));

    }

    public function trash(){
        $games = Game::onlyTrashed()->get();

        return view('admin.trash', compact('games'));
    }

    public function restore($id){
        $game = Game::withTrashed()->findOrFail($id);
        $game->teams()->restore();
        $game->quests()->restore();
        $game->restore();

        return redirect()->route('admin');
    }

    public function delete($id){
        $game = Game::withTrashed()->with('teams','quests')->findOrFail($id);

        foreach($game->quests as $q){
            $q->completedBy()->detach();
            $q->questions()->detach();
            $q->minigameImages()->detach();
        }

        foreach($game->teams as $t){
            $t->players()->detatch();
            $t->correctQuestions()->detatch();
            $t->foundDna()->detatch();
            $t->incorrectAnswers()->delete();
        }

        $game->teams()->forceDelete();
        $game->quests()->forceDelete();
        $game->forceDelete();

        return redirect()->route('admin');
    }

    public function siteMessages()
    {
        $messages = DB::table('globals')->get();
        $message_attributes = config('site_messages');


        $missing_config_entry = array_diff($messages->pluck('key')->all(), array_keys($message_attributes));
        $missing_db_entry = array_diff(array_keys($message_attributes), $messages->pluck('key')->all());
        if($missing_db_entry){
            foreach($missing_db_entry as $add_entry){
                DB::table('globals')->insert(['key' => $add_entry, 'message' => '']);
            }
            $messages = DB::table('globals')->get();
        }

        foreach($messages as $index => $message){
            if(!empty($message_attributes[$message->key])){
                foreach($message_attributes[$message->key] as $key => $value){
                    $message->{$key} = $value;
                }
            } else {
                unset($messages[$index]);
            }
        }

        return view('admin.siteMessages',compact('messages', 'missing_config_entry'));
    }

    public function updateSiteMessage(Request $request, $key)
    {

        if($request->{$key}){
            DB::table('globals')->where('key','=',$key)->update(['message' => $request->{$key}]);
        }

        return redirect()
            ->route('admin.siteMessages');
    }
}
