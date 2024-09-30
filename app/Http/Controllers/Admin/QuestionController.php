<?php

namespace App\Http\Controllers\Admin;

use App\Answer;
use App\Game;
use App\Http\Controllers\Controller;
use App\Location;
use App\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $locations = Location::get();
        $games = Game::get();
        $location = $request->input('location_id');
        $game = $request->input('game_id');
        $string = $request->input('search');

        $input = $request->all();

        $questions = Question::select();
        if (! empty($location)) {
            $questions->where('location_id', '=', $location);
        }

        if (! empty($game)) {
            $questions->whereHas('quests', function ($query) use ($game) {
                $query->where('game_id', '=', $game);
            });
        }
        if (! empty($string)) {
            $searchString = '%'.$string.'%';
            $questions->where('full_answer', 'like', $searchString)
                ->orWhere('text', 'like', $searchString);
        }

        $questions = $questions->with('incorrectAnswers')->get();

        return view('question.index', compact('locations', 'games', 'questions', 'input', 'location', 'game', 'string'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $question = new Question;
        $locations = Location::get();

        return view('question.create', compact('question', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //Validate
        $this->validate($request, [
            'text' => 'required',
            'full_answer' => 'required',
            'answer' => 'required',
            'location_id' => 'required',
        ]);

        // Load and fill question
        $question = new Question;
        $question->fill($request->all());
        $question->type = $request->type ? 1 : 0;
        // Add Image
        if ($request->file('new_image_file')) {
            $this->validate($request, [
                'new_image_file' => 'max:1024|mimetypes:image/jpeg,image/png,image/svg+xml',
            ]);
            $path = $request->file('new_image_file')->store('questions', 'public');
            $question->src = $path;
        }

        // Save question
        $question->save();

        // Update and set new answers
        $newAnswers = [];
        foreach ($request->answer as $key => $answerText) {
            if ($key == 'new') {
                foreach ($answerText as $newAnswer) {
                    $newAnswers[] = new Answer(['text' => $newAnswer]);
                }
            } else {
                $answer = Answer::find($key);
                $answer->text = $answerText;
                $answer->save();
            }
        }

        $question->answers()->saveMany($newAnswers);

        return redirect()->route('admin.question.index', ['id' => $question->id])->with('alert', ['message' => 'Question #'.$question->id.' created!', 'type' => 'success']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $question = Question::with('answers', 'incorrectAnswers')->findOrFail($id);
        $incorrect = [];
        foreach ($question->incorrectAnswers->groupBy('answer') as $k => $v) {
            $incorrect[] = ['answer' => $k, 'count' => $v->count()];
        }
        $incorrect = empty($incorrect) ? null : collect($incorrect);
        $locations = Location::get();

        return view('question.edit', compact('question', 'locations', 'incorrect'));
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

        //Validate
        $this->validate($request, [
            'text' => 'required',
            'full_answer' => 'required',
            'answer' => 'required',
            'location_id' => 'required',
        ]);

        // Load and fill question
        $question = Question::findOrFail($id);
        $question->fill($request->all());
        $question->type = $request->type ? 1 : 0;
        // Update Image
        if ($request->file('new_image_file')) {
            $this->validate($request, [
                'new_image_file' => 'max:1024|mimetypes:image/jpeg,image/png,image/svg+xml',
            ]);
            $path = $request->file('new_image_file')->store('questions', 'public');
            $question->deleteImage();
            $question->src = $path;
        }

        // Save question
        $question->save();

        // Update and set new answers
        $newAnswers = [];
        foreach ($request->answer as $key => $answerText) {
            if ($key == 'new') {
                foreach ($answerText as $newAnswer) {
                    $newAnswers[] = new Answer(['text' => $newAnswer]);
                }
            } else {
                $answer = Answer::find($key);
                $answer->text = $answerText;
                $answer->save();
            }
        }

        $question->answers()->saveMany($newAnswers);

        return redirect()->route('admin.question.index', ['id' => $question->id])->with('alert', ['message' => 'Question #'.$question->id.' updated!', 'type' => 'success']);
    }

    /**
     * Destroy the specified resource
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $question = Question::with('completedBy')->findOrFail($id);
        if ($question->completedBy->isEmpty()) {
            foreach ($question->answers as $answer) {
                $this->destroyAnswer($answer->id);
            }
            $question->quests()->detach();
            $alert = ['type' => 'success', 'message' => 'Question #'.$question->id.' deleted.'];
            $question->deleteImage();
            $question->delete();
        } else {
            $alert = ['type' => 'danger', 'message' => 'Question #'.$question->id.' could not be deleted. It is used in a previous game'];
        }

        return redirect()->route('admin.question.index')->with('alert', $alert);
    }

    /**
     * Destroy the question's answer
     *
     * @param  int  $id
     */
    public function destroyAnswer($id)
    {
        $answer = Answer::findOrFail($id);
        $answer->delete();
    }

    /**
     * Get input HTML for a new answer
     *
     * @return \Illuminate\Http\Response
     */
    public function newAnswer()
    {
        $answer = new Answer;

        return view('question._answer_input', compact('answer'));
    }
}
