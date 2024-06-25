@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.score',$game) !!}
@stop

@section('game.content')

    @if((empty($game->solutionSuspect) || empty($game->solutionLocation) || empty($game->solutionEvidence)))
    <div class="row">
        <div class="col-12 text-center">
            <p class="lead text-danger">The game solution is not set!</p>
            <p>Go to the <a href="{{ route('admin.game.edit', $game->id) }}">settings</a> and make sure a suspect, location, and evidence item is selected</p>
            <p>Otherwise, <strong>all teams will be shown with a correct indictment</strong>.</p>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-12">
            <p class="lead text-center">The final score!</p>
        </div>
        <div class="col-12 col-xs-6">
            <h3>Correct Solution</h3>
            <dl class="row" style="padding: 1em;">
                <dt class="col-sm-3">Suspect</dt>
                <dd class="col-sm-9">{{ $game->solutionSuspect->name ?? 'none' }}</dd>
                <dt class="col-sm-3">Location</dt>
                <dd class="col-sm-9">{{ $game->solutionLocation->name ?? 'none' }}</dd>
                <dt class="col-sm-3">Evidence</dt>
                <dd class="col-sm-9">{{ $game->solutionEvidence->title ?? 'none' }}</dd>
            </dl>
        </div>
        <div class="col-12 col-xs-6">
            @if($game->active)
                <h3>Bonus Points</h3>
                {{ html()->form('POST', route('admin.game.bonus', [$game->id]))->class('form-inline')->open() }}
                    <div class="form-group">
                        {{ html()->select('team_id', $teams->sortBy('name')->pluck('name', 'id')->all())->placeholder('Select a team')->class('form-control') }}
                    </div>
                    <div class="form-group">
                        {{ html()->number('points', 0)->class('form-control') }}
                    </div>
                    <button type="submit" class="btn btn-primary">Add points</button>

                {{ html()->form()->close() }}
                <span class="form-text">
                    You can remove bonus points by entering a negative number
                </span>
            @endif
        </div>
        <div class="col-12">
            <p class="text-center">
                <i class="fa fa-warning"></i>
                <a href="{{ route('admin.game.judgement', $game->id) }}">Remember to judge all answers!</a>
                <i class="fa fa-warning"></i>
            </p>
        </div>
        <div class="col-12">
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
                <tbody class="table-striped">
                @foreach($teams as $team)
                    <tr class="{{ $team->indictment_correct ? 'info' : 'danger' }}">
                        <td>{{ $team->name }}</td>
                        <td>{{ $team->correctQuestions->count() }}</td>
                        <td>{{ $team->foundDna->count() }}</td>
                        <td>
                            @if($team->indictment_correct)
                                Correct
                            @else
                                <dl class="row" style="max-width: 400px;">
                                    <dt class="col-sm-12 text-center text-capitalize">{{ $team->indictment_correct ? 'Correct' : 'Incorrect' }}</dt>
                                    <dt class="col-sm-3">Suspect</dt>
                                    <dd class="col-sm-9">{{ $team->suspect->name ?? 'none' }}</dd>
                                    <dt class="col-sm-3">Location</dt>
                                    <dd class="col-sm-9">{{ $team->location->name ?? 'none' }}</dd>
                                    <dt class="col-sm-3">Evidence</dt>
                                    <dd class="col-sm-9">{{ $team->evidence->title ?? 'none' }}</dd>
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
        <div class="col-12 text-end text-danger">
            <a class="btn btn-sm btn-warning" href="{{ route('admin.game.score',[$game->id,'waitlist']) }}">Score Waitlist Teams (for game testing purposes)</a>
        </div>
    </div>
@stop
