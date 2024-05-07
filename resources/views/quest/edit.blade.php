@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.quest.edit', $game, $quest, $quest->location) !!}
@stop

@section('game.content')
    @include('admin._alert')

    <div class="row">
        {!! Form::model($quest, ['route'=> ['admin.game.quest.update',$game->id,$quest->id], 'method' => 'put']) !!}
        <div class="col-12">
            <h1>{{ $quest->location->name }}</h1>
        </div>

        <div class="col-xs-12 col-sm-3">
            <div class="form-group">
                {!! Form::label('suspect_id','Suspect') !!}
                {!! Form::select('suspect_id',$suspects->pluck('name','id'), ($quest->suspect ? $quest->suspect->id : null), array('placeholder' => 'Select a suspect', 'class' => 'form-control')) !!}
            </div>

            <div class="form-group">
                {!! Form::label('location_id','Location') !!}
                {!! Form::select('location_id',$locations->pluck('name','id'), ($quest->location ? $quest->location->id : null), array('placeholder' => 'Select a location', 'class' => 'form-control')) !!}
            </div>

            <div class="form-group">
                {!! Form::label('type','Quest Type') !!}
                {!! Form::select('type',$quest->types, null, array('placeholder' => 'Select a type', 'class' => 'form-control')) !!}
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            <button type="button" class="btn btn-primary quest-type evidence-setup" data-toggle="modal" data-target="#importEvidenceRoomModal">
                Import From Previous Game
            </button>
        </div>
        <div class="col-xs-12 col-sm-9">

            <div class="quest-type question-setup">
                @include('quest._question_form')
            </div>

            <div class="quest-type minigame-setup">
                @include('quest._minigame_form')
            </div>

        </div>
        {!! Form::close() !!}
    </div>
@stop