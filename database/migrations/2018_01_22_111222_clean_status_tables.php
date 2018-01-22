<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Team;

class CleanStatusTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $qts = DB::table('quest_team')->get();
        $timestamp = Carbon::now()->toDateTimeString();
        foreach($qts as $qt) {
            if ($qt->created_at !== null) {
                $timestamp = $qt->created_at;
            } else {
                DB::table('quest_team')->where('team_id', $qt->team_id)->where('quest_id', $qt->quest_id)->update([
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ]);
            }
        }

        $teams = Team::all();
        foreach($teams as $team) {
            $completedQuests = $team->completedQuests()->get();
            foreach($completedQuests as $quest) {
                $completedAt = $quest->pivot->updated_at;
                $questionIds = $quest->questions->pluck('id');
                DB::table('question_team')->where('team_id', $team->id)->whereIn('question_id', $questionIds)->where('created_at', null)->update([
                    'created_at' => $completedAt,
                    'updated_at' => $completedAt
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
