<?php

namespace App\Http\Controllers;

use App\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Http\Requests;
use App\Suspect;
use App\Game;
use App\Team;
use App\GhostDna;
use App\Location;
use App\Question;
use App\IncorrectAnswer;
use Illuminate\Support\Facades\DB;

class UiController extends Controller
{

    /**
     * Show the game dashboard
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $game = Game::with('quests', 'quests.suspect', 'quests.questions')->find($request->session()->get('gameId'));
        if($request->session()->has('override_in_progress')){
            $game->start_time = Carbon::now()->subDay();
            $game->end_time = Carbon::now()->addDay();
        }

        $team = Team::with('correctQuestions','completedQuests','foundDna')->find($request->session()->get('teamId'));
        $dnaCount = GhostDna::get()->count();

        $progress = [];
        foreach ($game->quests as $q) {

            $percentComplete = '5';
            $progressMessage = '0 of ...';

            switch($q->type) {
                case 'minigame':
                    if ($team->completedQuests->contains('id',$q->id)){
                        $progressMessage = 'Complete';
                        $percentComplete = '100';
                    } else {
                        $progressMessage = 'Complete the Minigame';
                    }
                    break;
                case 'question':
                    $numQuestions = $q->questions->count();
                    $progressMessage = '0 of '.$numQuestions;


                    if($team->correctQuestions->isNotEmpty()){
                        $questQuestionIds = $q->questions->pluck('id')->all();
                        $answeredQuestionIds = $team->correctQuestions->pluck('id')->all();
                        $numAnswered = count(array_intersect($questQuestionIds, $answeredQuestionIds));
                        $progressMessage = $numAnswered.' of '.$numQuestions;
                        if(!empty($numAnswered) && !empty($numQuestions)){
                            $percentComplete = ceil($numAnswered / $numQuestions * 100);
                        } else {
                            $percentComplete = 1;
                        }

                    }
                    break;
            }



            $progress[$q->id] = [
                    'percentComplete' => $percentComplete,
                    'progressMessage' => $progressMessage,
            ];
        }

        return view('ui.index', compact('game','team','progress','dnaCount'));
    }

    /**
     * Show the game info
     *
     * @return \Illuminate\Http\Response
     */
    public function info()
    {
        return view('ui.info');
    }

    /**
     * Show the game map
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function map(Request $request)
    {
        $game = Game::with('evidenceLocation')->find($request->session()->get('gameId'));
        $floors = Location::has('quests')->with([
            'mapSection',
            'quests' => function($query) use ($request) { $query->where('game_id','=',$request->session()->get('gameId')); },
        ])->get();

        $floors = $floors->groupBy('floor');

        return view('ui.map',compact('floors', 'game'));

    }

    /**
     * Show the evidence minigame
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function evidence(Request $request)
    {
        $team = Team::with('evidence')->find($request->session()->get('teamId'));
        $game = Game::with('evidence', 'evidenceLocation', 'evidenceLocation.mapSection')->find($request->session()->get('gameId'));
        return view('ui.evidence', compact('game','team'));
    }

    /**
     * Show a quest location
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function quest(Request $request, $id)
    {
        $quest = Quest::with('suspect')->findOrFail($id);

        $team = Team::find($request->session()->get('teamId'));

        switch($quest->type){
            case 'question':
                $team->load('correctQuestions','completedQuests');
                break;
            case 'minigame':
                $quest->load('minigameImages');
                break;
        }
        return view('ui.quest', compact('quest','team'));
    }

    /**
     * Show the indictment interface
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function indictment(Request $request)
    {
        $team = Team::with('completedQuests','evidence', 'suspect', 'location')->find($request->session()->get('teamId'));
        $game = Game::with('quests', 'quests.suspect', 'quests.location', 'quests.location.mapSection')->find($request->session()->get('gameId'));
        $warnings = [];
        foreach($game->quests as $q){
            if(!$team->completedQuests->contains($q)){
                $warnings[] = [
                    'type' => 'quest',
                    'suspect' => $q->suspect,
                    'location' => $q->location,
                ];
            }
        }
        if(!$team->evidence){
            $warnings[] = [
                'type' => 'other',
                'text' => 'You need to go to the Fearringtonn Reading Room can figure out which Collection Item the Ghost has touched!',
            ];
        }
        return view('ui.indictment', compact('team','game','warnings'));
    }

    /**
     * Sets the teams indictment
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setIndictment(Request $request)
    {
        $team = Team::find($request->session()->get('teamId'));
        $this->validate($request,[
            'suspect' => 'required',
            'location' => 'required',
            'evidence' => 'required'
        ]);

        $team->suspect()->associate($request->suspect);
        $team->location()->associate($request->location);
        $team->evidence()->associate($request->evidence);
        $team->indictment_time = Carbon::now();

        $team->save();

        return redirect()->route('ui.index');
    }

    /**
     * Attempt a question, runs regex match of all the question's answers and the input attempt
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function attemptQuestion(Request $request, $id)
    {
        $this->validate($request,[
            'attempt' => 'required',
        ]);
        $teamId = $request->session()->get('teamId');
        $question = Question::with('answers','completedBy')->findOrFail($id);
        $attempt = $request->attempt;

        $pattern = '/('.$question->answers->implode('text',')|(').')/i';
        $response = [];

        if(preg_match($pattern,$attempt)) {
            if(!$question->completedBy->pluck('id')->contains($teamId)){
                $question->completedBy()->attach($teamId);
            }
            $response = ['correct' => true];

        } else {
            $question->incorrectAnswers()->create([
                'team_id' => $teamId,
                'answer' => $attempt,
            ]);
            $response = ['message' => "Incorrect Answer: $attempt"];
        }

        $question->save();

        return response()->json($response);
    }

    /**
     * Attempt a minigame answer
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function attemptMinigame(Request $request, $id)
    {
        $this->validate($request,[
            'attempt' => 'required',
        ]);

        $quest = Quest::with('minigameImages')->findOrFail($id);
        $correct1 = $quest->minigameImages->sortBy('year')->implode('id',',');
        $correct2 = $quest->minigameImages->sortByDesc('year')->implode('id',',');
        $attempt = $request->get('attempt');

        $response = [];

        if($attempt == $correct1 || $attempt == $correct2){
            $response = ['correct'=>true];
            $teamId = $request->session()->get('teamId');
            $quest->completedBy()->attach($teamId);
        }

        return response()->json($response);
    }

    /**
     * Show the DNA interface
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function dna(Request $request)
    {
        $sequences = GhostDna::get();
        $team = Team::with('foundDna')->find($request->session()->get('teamId'));
        foreach($sequences as $sequence){
            if($team->foundDna->contains($sequence)){
                $sequence->collected = true;
            } else {
                $sequence->collected = false;
            }
        }
        $sequences = array_values($sequences->groupBy('pair')->toArray());
        return view('ui.dna', compact('team','sequences'));
    }

    /**
     * Attempt DNA answer
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function attemptDna(Request $request)
    {
        $this->validate($request,[
            'attempt' => 'required',
        ]);
        $teamId = $request->session()->get('teamId');
        $dna = GhostDna::with('teams')->where('sequence','=',$request->attempt)->first();
        $response = [];

        if(!empty($dna)){
            if($dna->teams->pluck('id')->contains($teamId)){
                $response = ['message' => "Youe've already entered that sequence."];
            } else {
                $dna->teams()->attach($teamId);
                $dna->save();

                $sibling = GhostDna::where('pair','=',$dna->pair)->where('id','<>',$dna->id)->first();
                $response = [
                    'correct' => true,
                    'dnaId' => '#sequence-'.$dna->pair,
                    'topOrBottom' => $dna->id > $sibling->id ? 'bottom' : 'top',
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * Set a team's evidence choice
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setEvidence(Request $request)
    {
        $this->validate($request,[
            'evidence' => 'required',
        ]);
        $teamId = $request->session()->get('teamId');
        $team = Team::findOrFail($teamId);
        $team->evidence()->associate($request->evidence);
        $team->evidence_selected_at = Carbon::now();
        $team->save();

        return response()->json([]);
    }

    /**
     * Get a team's quest status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function questStatus(Request $request, $id)
    {

        $teamId = $request->session()->get('teamId');
        $team = Team::with('completedQuests')->findOrFail($teamId);
        $quest = Quest::findOrFail($id);


        $status = 'incomplete';
        $playerCount = $team->checkedInPlayers->count();

        switch($quest->type){
            case 'question':

                $team->load(['correctQuestions' => function($query) use ($quest) {
                    $query->ofQuest($quest->id);
                }]);

                $correct = $team->correctQuestions ? $team->correctQuestions->count() : 0;

                if($correct > 2) {
                    if(!$team->completedQuests->contains($quest)){
                        $team->completedQuests()->attach($quest);
                        $team->save();
                    }
                    $status = 'complete';
                    $message = trans('ui.questComplete',['count' => $playerCount]);
                } else {
                    $message = trans_choice('ui.questionStatus', $correct);
                }
                break;
            case 'minigame':
                $message = trans('ui.minigameStatus');
                if($team->completedQuests->contains($quest)){
                    $status = 'complete';
                    $message = trans('ui.questComplete',['count' => $playerCount]);
                }
                break;
            default:
                $message = '';
        }

        return response()->json(['status' => $status, 'message' => $message]);


    }

    /**
     * Retrieves any unread alerts
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function alert(Request $request)
    {
        $team = Team::with('game','game.alerts')->find($request->session()->get('teamId'));
        $seen = $request->session()->get('seen') ? $request->session()->get('seen') : [];

        $message = $team->game->alerts->reject(function($value, $key) use ($seen){
           return in_array($value->id, $seen);
        })->sortBy('created_at')->first();

        if($message){
            $response = ['html'=>'<p data-clear-alert="'.route('ui.alert.seen',$message->id).'">'.$message->message.'</p>'];
        } else {
            $response = [];
        }

        return response()->json($response);
    }

    /**
     * Sets an alert as seen
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function seen(Request $request, $id)
    {
        $team = Team::find($request->session()->get('teamId'))->first();
        $seen = $request->session()->get('seen') ? $request->session()->get('seen') : [];
        $seen[] = $id;
        $request->session()->put('seen',$seen);

        return response()->json(['success']);
    }


}
