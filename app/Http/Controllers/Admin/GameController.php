<?php

namespace App\Http\Controllers\Admin;

use App\Evidence;
use App\Game;
use App\Http\Controllers\Controller;
use App\Location;
use App\Player;
use App\Quest;
use App\Suspect;
use App\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $game = new Game;

        return view('game.create', compact('game'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'start_time' => 'required|date|after:today',
            'end_time' => 'required|date|after:start_time',
            'max_teams' => 'required|integer',
            'students_only' => 'required',
        ]);

        $game = new Game;
        $game->name = $request->get('name');
        $game->start_time = new Carbon($request->get('start_time'));
        $game->end_time = new Carbon($request->get('end_time'));
        $game->max_teams = $request->get('max_teams');
        $game->students_only = $request->get('students_only');
        $game->save();

        $suspects = Suspect::all();
        $quests = [];
        foreach ($suspects as $s) {
            switch ($s->id) {
                case '3':
                    $locationId = 4;
                    break;
                case '4':
                    $locationId = 7;
                    break;
                default:
                    $locationId = $s->id;
            }
            $quests[] = new Quest([
                'type' => 'question',
                'suspect_id' => $s->id,
                'location_id' => $locationId,
            ]);
        }

        $game->quests()->saveMany($quests);

        return redirect()->route('admin.game.show', $game->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $game = Game::with(
            ['registeredTeams' => function ($query) {
            $query->orderBy('name');
            }],
            'waitlistTeams',
            'solutionSuspect',
            'solutionLocation',
            'solutionEvidence',
            'evidence',
            'evidenceLocation',
            'geographicInvestigationLocation',
            'quests.suspect',
            'quests.location'
        )->findOrFail($id);

        $warnings = $this->getWarnings($game);

        $players = Player::ofGame($id)->get();

        return view('game.dashboard', compact('game', 'players', 'warnings'));
    }

    /**
     * Display the game settings, quest locations, solution, and evidence information
     * with edit modals for solution and settings
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $game = Game::with(
            'solutionSuspect',
            'solutionLocation',
            'solutionEvidence',
            'evidence',
            'evidenceLocation',
            'geographicInvestigationLocation',
            'quests.suspect',
            'quests.location',
            'quests.questions'
        )->findOrFail($id);

        $warnings = $this->getWarnings($game);
        $locations = Location::get();

        return view('game.edit', compact('game', 'warnings', 'locations'));
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
        $game = Game::findOrFail($id);

        if ($request->cf_item) {
            $cfInput = $request->input('cf_item');
            $cfItems = [];
            foreach ($cfInput['title'] as $key => $value) {
                $cfItems[$key] = [
                    'title' => $value,
                    'type' => $cfInput['type'][$key],
                    'text' => $cfInput['text'][$key],
                ];
            }
            $game->case_file_items = $cfItems;
        }
        if ($request->evidence_list) {
            $add_evidence = explode(',', $request->input('evidence_list'));
            if (! in_array($game->evidence_id, $add_evidence)) {
                $game->evidence_id = 0;
            }
            $game->evidence()->detach();
            $game->evidence()->attach($add_evidence);
        }
        if ($request->end_time) {
            $request->end_time = date('Y-m-d H:i:s', strtotime($request->end_time));
        }
        if ($request->end_time) {
            $request->start_time = date('Y-m-d H:i:s', strtotime($request->start_time));
        }

        // activate the game when opening registration
        if ($request->registration && $request->registration == 1) {
            $activeGame = Game::active()->get()->first();
            if (! empty($activeGame)) {
                $activeGame->active = false;
                $activeGame->save();
            }
            $game->active = true;
        }

        foreach ($game->getAttributes() as $key => $value) {
            if (isset($request->{$key}) && $value !== $request->{$key}) {
                $game->{$key} = $request->{$key};
            }
        }

        $game->save();

        return redirect()->back()->with('alert', ['message' => 'Game Updated', 'type' => 'success']);
    }

    /**
     * Soft delete a game from storage
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $game = Game::findOrFail($id);
        $game->teams()->delete();
        $game->quests()->delete();
        $game->active = false;
        $game->save();
        $game->delete();

        return redirect()->route('admin');
    }

    /**
     * Activate the game
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        $activeGame = Game::active()->get()->first();
        $game = Game::findOrFail($id);

        if (! empty($activeGame) && $game->id != $activeGame->id) {
            $activeGame->active = false;
            $activeGame->save();
        }

        $game->active = true;
        $game->save();

        return redirect()->back();
    }

    /**
     * Deactivate the game
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deactivate($id)
    {
        $game = Game::findOrFail($id);
        $game->active = false;
        $game->save();

        return redirect()->back();
    }

    /**
     * Show the form for editing the post game display
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editArchive($id)
    {
        $game = Game::with('registeredTeams', 'winningTeam')->findOrFail($id);

        return view('game.archiveData', compact('game'));
    }

    /**
     * Show the form for editing the game's evidence room
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editEvidence($id)
    {
        $game = Game::with('evidence', 'evidenceLocation')->findOrFail($id);
        $attachedEvidence = $game->evidence ? $game->evidence->pluck('id')->all() : [];
        $evidence = Evidence::whereNotIn('id', $attachedEvidence)
            ->get();
        $locations = Location::get();
        $games = Game::where('id', '!=', $id);

        return view('game.evidence', compact('game', 'evidence', 'locations', 'games'));
    }

    /**
     * Import evidence room information from a previous game
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function importEvidenceRoom(Request $request, $id)
    {
        $this->validate($request, [
            'game_id' => 'required',
        ]);

        $previousGameId = $request->input('game_id');

        if ($id !== $previousGameId) {
            $previousGame = Game::with(['evidence'])->findOrFail($previousGameId);

            $game = Game::findOrFail($id);

            $game->evidence()->detach();
            $game->evidence()->attach($previousGame->evidence->pluck('id')->all());
            $game->case_file_items = $previousGame->case_file_items;
            $game->evidence_location_id = $previousGame->evidence_location_id;
            $game->save();
        }

        return redirect()->back();
    }

    /**
     * Show the list of teams associated with a game
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function teams($id)
    {
        $game = Game::with(
            ['registeredTeams' => function ($query) {
            $query->orderBy('name')->registered();
            }],
            'registeredTeams.players',
            ['waitlistTeams' => function ($query) {
            $query->orderBy('name')->registered();
            }],
            'waitlistTeams.players'
        )->findOrFail($id);

        return view('game.teams', ['game' => $game]);
    }

    /**
     * Show the list of quests associated with a game
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function quests($id)
    {
        $game = Game::with(['quests.location', 'quests.suspect', 'quests.questions'])->findOrFail($id);

        return view('quest.index', compact('game'));
    }

    /**
     * Associated a new team with a game
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addTeam(Request $request, $id)
    {
        $this->validate($request, ['name' => 'required']);
        $team = new Team();
        $team->fill($request->all());
        $game = Game::findOrFail($id);
        $game->teams()->save($team);

        return redirect()->back()->with('alert', ['type' => 'success', 'message' => $team->name.' added']);
    }

    /**
     * Show the judge questions interface
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function judgement($id)
    {
        $game = Game::with('quests')->findOrFail($id);
        $quests = $game->quests;

        return view('game.judgement', compact('game', 'quests'));
    }

    /**
     * Set a question to correct and mark all answers as judged
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  int  $questionId
     * @param  int  $teamId
     * @return \Illuminate\Http\Response
     */
    public function judgeAnswers(Request $request, $id, $questId, $questionId, $teamId)
    {
        $this->validate($request, ['judgement' => 'required']);
        $team = Team::findOrFail($teamId);

        if ($request->get('judgement') == 'correct') {
            $team->correctQuestions()->attach($questionId);
            $team->save();

            if ($team->correctQuestions()->count() > 2) {
                $team->completedQuests()->attach($questId);
                $team->save();
            }
        }
        DB::table('incorrect_answers')->where('question_id', $questionId)->where('team_id', $teamId)->update(['judged' => 1]);

        return redirect()->back();
    }

    /**
     * Tally team scores and display results
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function score($id, $includeWaitlist = false)
    {
        $scoring = config('scoring');
        $game = Game::with('solutionSuspect', 'solutionLocation', 'solutionEvidence')->findOrFail($id);
        $teams = $includeWaitlist == 'waitlist' ?
                    $game->waitlistTeams :
                    $game->registeredTeams;

        if ($game->active) {
            $timeBonus = $scoring['time_bonus']['starting_value'];

            foreach ($teams->sortBy('indictment_time') as $team) {
                $teams->load('evidence', 'suspect', 'location');
                $qScore = $team->correctQuestions->count() * $scoring['questions'];
                $dnaScore = $team->foundDna->count() * $scoring['dna']['each'];
                $dnaPairScore = $team->foundDna->groupBy('pair')->count() * $scoring['dna']['each'];
                $team->score = $qScore + $dnaScore + $dnaPairScore;
                if ($team->indictment_correct) {
                    $team->score += $timeBonus;
                    $timeBonus -= $scoring['time_bonus']['decrement'];
                }
                $team->save();
            }
        }

        $correct_indictments = $teams->filter(function ($team) {
            return $team->indictment_correct == true;
        })->sortByDesc('score');
        $incorrect_indictments = $teams->filter(function ($team) {
            return $team->indictment_correct == false;
        })->sortByDesc('score');
        $teams = $correct_indictments->merge($incorrect_indictments);

        return view('game.score', compact('game', 'teams'));
    }

    /**
     * Add bonus points to a team
     *
     * @param  Request  $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bonusPoints(Request $request, $id)
    {
        $this->validate($request, [
            'team_id' => 'required|exists:teams,id',
            'points' => 'required|integer',
        ]);

        $team = Team::findOrFail($request->team_id);
        $team->bonus_points = $team->bonus_points + $request->points;
        $team->save();

        return redirect()->route('admin.game.score', $id);
    }

    /**
     * Show the interface for checking in players
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkIn($id)
    {
        $game = Game::with('registeredTeams', 'registeredTeams.players')->findOrFail($id);

        return view('game.checkin', compact('game'));
    }

    /**
     * Check-in the player
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkInPlayer(Request $request, $id, $playerId = false)
    {
        if ($playerId) {
            $player = Player::find($playerId);
        } else {
            $this->validate($request, ['pid' => 'required']);
            $player = Player::where('pid', $request->get('pid'))->first();
        }

        if ($player) {
            if ($player->checked_id) {
                $return = redirect()->route('admin.game.checkin', $id)->with(
                    'alert', [
                        'type' => 'warning',
                        'message' => $player->full_name.' already checked in. Team: '.$player->teams()->active()->first()->name,
                    ]
                );
            } else {
                $player->checked_in = true;
                $player->save();
                $return = redirect()->route('admin.game.checkin', $id)->with(
                    'alert', [
                        'type' => 'success',
                        'message' => $player->full_name.' successfully checked in. Team: '.$player->teams()->active()->first()->name,
                    ]
                );
            }
        } else {
            $return = redirect()->route('admin.game.checkin', $id)->with(
                'alert', [
                    'type' => 'danger',
                    'message' => 'Could not find a player with a PID: '.$request->get('pid'),
                ]
            );
        }

        return $return;
    }

    /**
     * Sets a session variable that allows users to test the game even if it is not in progress
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function overrideInProgress(Request $request)
    {
        $game = Game::active()->first();
        $request->session()->push('override_in_progress', $game->id);

        return redirect()->route('ui.index');
    }

    public function getWarnings(Game $game)
    {
        $warnings = [];
        if (empty($game->solutionSuspect)) {
            $warnings[] = 'No Suspect selected for solution';
        }
        if (empty($game->solutionLocation)) {
            $warnings[] = 'No Location selected for solution';
        }
        if (empty($game->solutionEvidence)) {
            $warnings[] = 'No Evidence item selected for solution. You will need to set the evidence location, case files, and items.';
        }
        if (empty($game->evidenceLocation)) {
            $warnings[] = 'No Evidence Room location is set.';
        }
        if (empty($game->geographicInvestigationLocation)) {
            $warnings[] = 'No Geographic Investigation location is set.';
        }

        foreach ($game->quests as $quest) {
            if ($game->quests->filter(function ($value) use ($quest) {
            return $value->location->id == $quest->location->id;
            })->count() > 1) {
                $warnings[] = $quest->location->name.' is used in multiple quests';
            }
            if ($game->quests->filter(function ($value) use ($quest) {
            return $value->suspect->id == $quest->suspect->id;
            })->count() > 1) {
                $warnings[] = $quest->suspect->name.' is used in multiple quests';
            }
        }

        return array_unique($warnings);
    }
}
