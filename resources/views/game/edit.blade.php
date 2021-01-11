@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.edit',$game) !!}
@stop

@section('game.content')
    @include('game._warnings')

    <div class="row">

        <div class="col-xs-12 text-center">
            @include('partials._delete_form', ['route' => ['admin.game.destroy', $game->id]])
            <p class="lead">
                Edit the game settings, solution, quest locations, and evidence room
            </p>
        </div>

        <div class="col-xs-12">
            @include('admin._alert')
        </div>

        <!-- Game Settings -->
        <div class="col-xs-12">
            <h2 class="expo">Settings</h2>
        </div>
        <!-- Options -->
        <div class="col-xs-12 col-sm-6">
            <div class="dash-section">
                <div class="dash-section-header">
                    <button type="button" class="btn btn-primary btn-sm pull-right" data-toggle="modal" data-target="#gameOptions">
                        <span class="fa fa-edit"></span> Edit
                    </button>
                    <h3>
                        Options
                    </h3>
                </div>
                <div class="dash-section-body">
                    <dl>
                        <dt>Start Time</dt>
                        <dd>{{ $game->start_time->format('h:i A') }}</dd>
                        <dt>
                            End Time
                            @if($game->end_time->format('mdY') != $game->start_time->format('mdY'))
                                <small class="text-danger">
                                    <span class="fa fa-warning"></span>
                                    Game ending on different day
                                </small>
                            @endif
                        </dt>
                        <dd>{{ $game->end_time->format('h:i A') }}</dd>
                        <dt>Max Teams</dt>
                        <dd>{{ $game->max_teams }}</dd>
                        <dt>Player Restrictions</dt>
                        <dd>{{ $game->students_only ? 'UNC Students Only' : 'Any UNC Affiliate w/ ONYEN' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Scoring -->
        <div class="col-xs-12 col-sm-6">
            <div class="dash-section">
                <div class="dash-section-header">
                    <button type="button" class="btn btn-primary btn-sm pull-right" data-toggle="modal" data-target="#gameSolution">
                        <span class="fa fa-edit"></span> Edit
                    </button>
                    <h3>Solution</h3>
                </div>
                <div class="dash-section-body">
                    <ul class="list-unstyled">
                        <li><strong>Ghost:</strong> {{ $game->solutionSuspect->name ?? 'No Suspect Selected'}}</li>
                        <li><strong>Portal:</strong> {{ $game->solutionLocation->name ?? 'No Location Selected'}}</li>
                        <li><strong>Evidence:</strong> {{ $game->solutionEvidence->title ?? 'No Evidence Selected' }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quests -->
        <div class="col-xs-12">
            <h2 class="expo">Quest Locations</h2>
            @foreach($game->quests as $quest)
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="well">
                    <h3>{{ $quest->location->name }}</h3>
                    <ul class="list-unstyled">
                        @if(!empty($quest->suspect))
                            <li><strong>{{ $quest->suspect->name }}</strong></li>
                        @endif
                        <li>
                            <strong>Type:</strong><span class="text-capitalize"> {{ $quest->type }}</span>
                            @if($quest->type == 'question')
                                &nbsp;({{ $quest->questions->count() }} questions)
                            @endif
                        </li>
                    </ul>
                    <a href="{{ route('admin.game.quest.edit',array($game->id,$quest->id)) }}" class="btn btn-primary">
                        <span class="fa fa-edit"></span> Edit
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Geographic Investigation Location -->
        <div class="col-xs-12">
            <h2 class="expo">Geographic Investigation</h2>
        </div>
        <div class="col-xs-12">
            <div class="dash-section">
                {!! Form::model($game, ['route'=> ['admin.game.update',$game->id], 'method' => 'put']) !!}
                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        {!! Form::label('geographic_investigation_location_id','Location') !!}
                        {!! Form::select(
                            'geographic_investigation_location_id',
                            $locations->pluck('name','id'),
                            ($game->geographic_investigation_location_id ? $game->geographic_investigation_location_id : null),
                            array('placeholder' => 'Select a location', 'class' => 'form-control'))
                        !!}
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <!-- Evidence -->
        <div class="col-xs-12">
            <h2 class="expo">Evidence Room</h2>
        </div>
        <div class="col-xs-12">
            <div class="dash-section">
                <div class="dash-section-header">
                    <a href="{{ route('admin.game.edit.evidence', $game->id) }}" type="button" class="btn btn-primary btn-sm pull-right">
                        <span class="fa fa-edit"></span> Edit
                    </a>
                    <h3>Images</h3>
                </div>
                <div class="dash-section-body">
                    @include('evidence._list',['evidence' => $game->evidence])
                </div>
            </div>
        </div>

        @if($game->case_file_items)
            @foreach($game->case_file_items as $cf_item)
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="dash-section">
                    <div class="dash-section-header">
                        <h3>{{ $cf_item->title }}</h3>
                    </div>
                    <div class="dash-section-body">
                        {!! app(Parsedown::class)->text($cf_item->text) !!}
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
@stop

@section('modal')
    @include('game.solution_edit_modal')
    @include('game.options_edit_modal')
@stop