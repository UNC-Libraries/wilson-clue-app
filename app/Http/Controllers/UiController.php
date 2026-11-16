<?php

namespace App\Http\Controllers;

use App\Game;
use App\Evidence;
use App\GhostDna;
use App\Location;
use App\Quest;
use App\Question;
use App\Team;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UiController extends Controller
{
    /**
     * Show the game dashboard
     *
     * @return Factory|View
     */
    public function index(Request $request): Factory|View
    {
        $game = Game::with('quests', 'quests.suspect', 'quests.questions')->find($request->session()->get('gameId'));
        if ($request->session()->has('override_in_progress')) {
            $game->start_time = Carbon::now()->subDay();
            $game->end_time = Carbon::now()->addDay();
        }

        $team = $this->currentTeam($request)->load('correctQuestions', 'completedQuests', 'foundDna');
        $dnaCount = GhostDna::get()->count();

        $progress = [];
        foreach ($game->quests as $q) {
            $percentComplete = '5';
            $progressMessage = '0 of ...';

            switch ($q->type) {
                case 'minigame':
                    if ($team->completedQuests->contains('id', $q->id)) {
                        $progressMessage = 'Complete';
                        $percentComplete = '100';
                    } else {
                        $progressMessage = 'Complete the Minigame';
                    }
                    break;
                case 'question':
                    $numQuestions = $q->questions->count();
                    $progressMessage = '0 of '.$numQuestions;

                    if ($team->correctQuestions->isNotEmpty()) {
                        $questQuestionIds = $q->questions->pluck('id')->all();
                        $answeredQuestionIds = $team->correctQuestions->pluck('id')->all();
                        $numAnswered = count(array_intersect($questQuestionIds, $answeredQuestionIds));
                        $progressMessage = $numAnswered.' of '.$numQuestions;
                        if (! empty($numAnswered) && ! empty($numQuestions)) {
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

        return view('ui.index', compact('game', 'team', 'progress', 'dnaCount'));
    }

    /**
     * Show the game info
     *
     * @return Factory|View
     */
    public function info(): Factory|View
    {
        return view('ui.info');
    }

    /**
     * Show the game map
     *
     * @return Factory|View
     */
    public function map(Request $request): Factory|View
    {
        $game = Game::with('evidenceLocation', 'geographicInvestigationLocation')->find($request->session()->get('gameId'));
        $floors = Location::has('quests')->with([
            'quests' => function ($query) use ($request) {
                $query->where('game_id', '=', $request->session()->get('gameId'));
            },
        ])->get();

        $floors = $floors->groupBy('floor');

        return view('ui.map', compact('floors', 'game'));
    }

    /**
     * Show the evidence minigame
     *
     * @return Factory|View
     */
    public function evidence(Request $request): Factory|View
    {
        $team = $this->currentTeam($request)->load('evidence');
        $game = Game::with('evidence', 'evidenceLocation')->find($request->session()->get('gameId'));

        return view('ui.evidence', compact('game', 'team'));
    }

    /**
     * Show the geographic investigation information
     *
     * @return Factory|View
     */
    public function geographicInvestigation(Request $request): Factory|View
    {
        $game = Game::with('geographicInvestigationLocation')->find($request->session()->get('gameId'));

        return view('ui.geographic_investigation', compact('game'));
    }

    /**
     * Show a quest location
     *
     * @return Factory|View
     */
    public function quest(Request $request, $id): Factory|View
    {
        $quest = $this->questForCurrentGame($request, (int) $id)->load('suspect');

        $team = $this->currentTeam($request);

        switch ($quest->type) {
            case 'question':
                $team->load('correctQuestions', 'completedQuests');
                break;
            case 'minigame':
                $quest->load('minigameImages');
                break;
        }

        return view('ui.quest', compact('quest', 'team'));
    }

    /**
     * Show the indictment interface
     *
     * @return Factory|View
     */
    public function indictment(Request $request): Factory|View
    {
        $team = $this->currentTeam($request)->load('completedQuests', 'evidence', 'suspect', 'location');
        $game = Game::with('quests', 'quests.suspect', 'quests.location')->find($request->session()->get('gameId'));
        $warnings = [];
        foreach ($game->quests as $q) {
            if (! $team->completedQuests->contains($q)) {
                $warnings[] = [
                    'type' => 'quest',
                    'suspect' => $q->suspect,
                    'location' => $q->location,
                ];
            }
        }
        if (! $team->evidence) {
            $warnings[] = [
                'type' => 'other',
                'text' => 'You need to go to the Fearringtonn Reading Room can figure out which Collection Item the Ghost has touched!',
            ];
        }

        return view('ui.indictment', compact('team', 'game', 'warnings'));
    }

    /**
     * Sets the teams indictment
     *
     * @return RedirectResponse
     */
    public function setIndictment(Request $request): RedirectResponse
    {
        $team = $this->currentTeam($request);
        $request->validate([
            'suspect' => ['required', 'integer'],
            'location' => ['required', 'integer'],
            'evidence' => ['required', 'integer'],
        ]);

        $game = Game::findOrFail($this->currentGameId($request));
        abort_unless(
            $game->suspect_id == $request->integer('suspect')
                && $game->location_id == $request->integer('location')
                && $game->evidence_id == $request->integer('evidence'),
            422
        );

        $team->suspect_id = $request->integer('suspect');
        $team->location_id = $request->integer('location');
        $team->evidence_id = $request->integer('evidence');
        if (empty($team->evidence_selected_at)) {
            $team->evidence_selected_at = Carbon::now()->subSeconds(5);
        }
        $team->indictment_time = Carbon::now();

        $team->save();

        return redirect()->route('ui.index');
    }

    /**
     * Attempt a question, runs regex match of all the question's answers and the input attempt
     *
     * @param int $id
     * @return JsonResponse
     */
    public function attemptQuestion(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'attempt' => 'required',
        ]);
        $team = $this->currentTeam($request);
        $teamId = $team->id;
        $question = $this->questionForCurrentGame($request, (int) $id)
            ->load('answers', 'completedBy');
        $attempt = $request->attempt;

        $pattern = '/('.$question->answers->implode('text', ')|(').')/i';
        $response = [];

        if (preg_match($pattern, $attempt)) {
            if (! $question->completedBy->pluck('id')->contains($teamId)) {
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
     * @param int $id
     * @return JsonResponse
     */
    public function attemptMinigame(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'attempt' => 'required',
        ]);

        $quest = $this->questForCurrentGame($request, (int) $id)
            ->load('minigameImages');
        $correct1 = $quest->minigameImages->sortBy('year')->implode('id', ',');
        $correct2 = $quest->minigameImages->sortByDesc('year')->implode('id', ',');
        $attempt = $request->input('attempt');

        $response = [];

        if ($attempt == $correct1 || $attempt == $correct2) {
            $response = ['correct' => true];
            $team = $this->currentTeam($request);
            $quest->completedBy()->attach($team->id);
        }

        return response()->json($response);
    }

    /**
     * Show the DNA interface
     *
     * @return Factory|View
     */
    public function dna(Request $request): Factory|View
    {
        $sequences = GhostDna::get();
        $team = $this->currentTeam($request)->load('foundDna');
        foreach ($sequences as $sequence) {
            if ($team->foundDna->contains($sequence)) {
                $sequence->collected = true;
            } else {
                $sequence->collected = false;
            }
        }
        $sequences = array_values($sequences->groupBy('pair')->toArray());

        return view('ui.dna', compact('team', 'sequences'));
    }

    /**
     * Attempt DNA answer
     *
     * @return JsonResponse
     */
    public function attemptDna(Request $request): JsonResponse
    {
        $request->validate([
            'attempt' => 'required',
        ]);
        $teamId = $this->currentTeam($request)->id;
        $dna = GhostDna::with('teams')->where('sequence', '=', $request->attempt)->first();
        $response = [];

        if (! empty($dna)) {
            if ($dna->teams->pluck('id')->contains($teamId)) {
                $response = ['message' => "Youe've already entered that sequence."];
            } else {
                $dna->teams()->attach($teamId);
                $dna->save();

                $sibling = GhostDna::where('pair', '=', $dna->pair)->where('id', '<>', $dna->id)->first();
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
     * @return JsonResponse
     */
    public function setEvidence(Request $request): JsonResponse
    {
        $team = $this->currentTeam($request);
        $gameId = $this->currentGameId($request);

        $request->validate([
            'evidence' => ['required', 'integer'],
        ]);

        $evidence = Evidence::whereHas('games', function ($query) use ($gameId) {
            $query->whereKey($gameId);
        })->findOrFail($request->integer('evidence'));

        $team->evidence()->associate($evidence);
        $team->evidence_selected_at = Carbon::now();
        $team->save();

        return response()->json([]);
    }

    /**
     * Get a team's quest status
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function questStatus(Request $request, $id): JsonResponse
    {
        $team = $this->currentTeam($request)->load('completedQuests');
        $quest = $this->questForCurrentGame($request, (int) $id);

        $status = 'incomplete';
        $playerCount = $team->checkedInPlayers->count();

        switch ($quest->type) {
            case 'question':

                $team->load(['correctQuestions' => function ($query) use ($quest) {
                    $query->ofQuest($quest->id);
                }]);

                $correct = $team->correctQuestions ? $team->correctQuestions->count() : 0;

                if ($correct > 2) {
                    if (! $team->completedQuests->contains($quest)) {
                        $team->completedQuests()->attach($quest);
                        $team->save();
                    }
                    $status = 'complete';
                    $message = trans('ui.questComplete', ['count' => $playerCount]);
                } else {
                    $message = trans_choice('ui.questionStatus', $correct);
                }
                break;
            case 'minigame':
                $message = trans('ui.minigameStatus');
                if ($team->completedQuests->contains($quest)) {
                    $status = 'complete';
                    $message = trans('ui.questComplete', ['count' => $playerCount]);
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
     * @return JsonResponse
     */
    public function alert(Request $request): JsonResponse
    {
        $team = $this->currentTeam($request)->load('game.alerts');
        $seen = $request->session()->get('seen') ? $request->session()->get('seen') : [];

        $message = $team->game->alerts->reject(function ($value, $key) use ($seen) {
            return in_array($value->id, $seen);
        })->sortBy('created_at')->first();

        if ($message) {
            $response = ['html' => '<p data-clear-alert="'.route('ui.alert.seen', $message->id).'">'.$message->message.'</p>'];
        } else {
            $response = [];
        }

        return response()->json($response);
    }

    /**
     * Sets an alert as seen
     *
     * @param int $id
     * @return JsonResponse
     */
    public function seen(Request $request, int $id): JsonResponse
    {
        $team = $this->currentTeam($request);
        $seen = $request->session()->get('seen') ? $request->session()->get('seen') : [];
        $seen[] = $id;
        $request->session()->put('seen', $seen);

        return response()->json(['success']);
    }

    /**
     * Get the team validated by the ValidTeam middleware.
     */
    private function currentTeam(Request $request): Team
    {
        return $request->attributes->get('currentTeam') ?? abort(403);
    }

    /**
     * Get the current game ID from the player's session.
     */
    private function currentGameId(Request $request): int
    {
        return (int) $request->session()->get('gameId');
    }

    /**
     * Find a quest belonging to the current game.
     */
    private function questForCurrentGame(Request $request, int $id): Quest
    {
        return Quest::where('game_id', $this->currentGameId($request))
            ->findOrFail($id);
    }

    /**
     * Find a question attached to a quest in the current game.
     */
    private function questionForCurrentGame(Request $request, int $id): Question
    {
        return Question::whereHas('quests', function ($query) use ($request) {
            $query->where('game_id', $this->currentGameId($request));
        })->findOrFail($id);
    }
}
