@foreach($quest->questions->where('needs_judgement', true) as $question)
    <div class="row">
        @if($question->type)
            <div class="col-12 col-xs-2">
                <img class="img-fluid" src="{{ asset($question->src) }}">
            </div>
        @endif
        <div class="col-12 col-xs-10">
            <h3>{{ $question->text }}</h3>
        </div>
        <div class="col-12">
            <dl>
                <dt>Full Answer</dt>
                <dd>{{ $question->full_text }}</dd>
                <dt>Answers in app</dt>
                <dd>{{ $question->answers->implode('text',', ') }}</dd>
            </dl>
        </div>
    </div>
    <div class="row">
        @foreach($question->not_judged_answers->groupBy('team_id') as $answers)
            <div class="col-12 col-xs-6 col-sm-4 judge-team" id="judge-team-{{ $answers->first()->team_id }}">
                <div class="card card-body">
                    <div class="row">
                        <div class="col-6">
                            {!! Form::open(['route' => ['admin.game.judgeAnswers', $game->id, $quest->id, $question->id, $answers->first()->team_id]]) !!}
                            <input type="hidden" name="judgement" value="wrong">
                            <button type="submit" class="btn btn-danger">
                                <span class="fa fa-times"></span> Wrong
                            </button>
                            {!! Form::close() !!}
                        </div>
                        <div class="col-6">
                            {!! Form::open(['route' => ['admin.game.judgeAnswers', $game->id, $quest->id, $question->id, $answers->first()->team_id]]) !!}
                            <input type="hidden" name="judgement" value="correct">
                            <button type="submit" class="btn btn-success float-right">
                                <span class="fa fa-check"></span> Correct
                            </button>
                            {!! Form::close() !!}
                        </div>
                        <div class="col-12 text-center">
                            <p class="lead">{{ $answers->first()->team->name }}</p>
                            <p>{{ $answers->implode('answer', ', ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <hr>
@endforeach