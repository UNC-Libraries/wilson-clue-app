@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.score',$game) !!}
@stop

@section('game.content')

    @if(empty($game->solutionSuspect) || empty($game->solutionLocation) || empty($game->solutionEvidence))
        <div class="row">
            <div class="col-xs-12 text-center">
                <p class="lead text-danger">The game solution is not set!</p>
                <p>Go to the <a href="{{ route('admin.game.edit', $game->id) }}">settings</a> and make sure a suspect, location, and evidence item is selected</p>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xs-12">
                <p class="lead text-center">The final score!</p>
            </div>
            <div class="col-xs-12 col-sm-6">
                <h3>Correct Solution</h3>
                <dl>
                    <dt>Suspect</dt>
                    <dd>{{ $game->solutionSuspect->name }}</dd>
                    <dt>Location</dt>
                    <dd>{{ $game->solutionLocation->name }}</dd>
                    <dt>Evidence</dt>
                    <dd>{{ $game->solutionEvidence->title }}</dd>
                </dl>
            </div>
            <div class="col-xs-12 col-sm-6">
                <h3>Bonus Points</h3>
                {!! Form::open(['route' => ['admin.game.bonus', $game->id], 'class'=> 'form-inline']) !!}
                    <div class="form-group">
                        {!! Form::select('team_id',$teams->sortBy('name')->pluck('name','id')->all(),null,['placeholder'=>'Select a team', 'class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::number('points',0,['class' => 'form-control']) !!}
                    </div>
                    <button type="submit" class="btn btn-primary">Add points</button>
                {!! Form::close() !!}
                <span class="help-block">
                    You can remove bonus points by entering a negative number
                </span>
            </div>
            <div class="col-xs-12">
                <p class="text-center">
                    <i class="fa fa-warning"></i>
                    <a href="{{ route('admin.game.judgement', $game->id) }}">Remember to judge all answers!</a>
                    <i class="fa fa-warning"></i>
                </p>
            </div>
            <div class="col-xs-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Team Name</th>
                            <th>Correct Questions</th>
                            <th>Correct DNA</th>
                            <th>Indictment</th>
                            <th>Indictment Time (Indictment Bonus)</th>
                            <th>Additional Bonus Points</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($teams->sortByDesc('score') as $team)
                        <tr class="{{ $team->indictment_correct ? 'info' : 'danger' }}">
                            <td>{{ $team->name }}</td>
                            <td>{{ $team->correctQuestions->count() }}</td>
                            <td>{{ $team->foundDna->count() }}</td>
                            <td>
                            @if($team->indictment_correct)
                                Correct
                            @else
                                <dl class="list-unstyled">
                                    <dt>{{ $team->indictment_correct ? 'Correct' : 'Incorrect' }}</dt>
                                    <dt>Suspect</dt>
                                    <dd>{{ $team->suspect->name or 'none' }}</dd>
                                    <dt>Location</dt>
                                    <dd>{{ $team->location->name or 'none' }}</dd>
                                    <dt>Evidence</dt>
                                    <dd>{{ $team->evidence->title or 'none' }}</dd>
                                </dl>
                            @endif
                            </td>
                            <td>{{ $team->indictment_time ? $team->indictment_time->format('g:i:s A') : 'Indictment not submitted' }}</td>
                            <td>{{ $team->bonus_points }}</td>
                            <td>{{ $team->score }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 text-right text-danger">
                <a class="btn btn-sm btn-warning" href="{{ route('admin.game.score',[$game->id,'waitlist']) }}">Score Waitlist Teams (for game testing purposes)</a>
            </div>
        </div>
    @endif
@stop
