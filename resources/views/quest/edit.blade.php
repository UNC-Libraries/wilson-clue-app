@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.quest.edit', $game, $quest, $quest->location) !!}
@stop

@section('game.content')
    @include('admin._alert')

    <div class="row">
        {{ html()->modelForm($quest, 'PUT', route('admin.game.quest.update', [$game->id, $quest->id]))->open() }}
        <div class="col-xs-12">
            <h1>{{ $quest->location->name }}</h1>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="form-group">
                {{ html()->label('Suspect', 'suspect_id') }}
                {{ html()->select('suspect_id', $suspects->pluck('name', 'id'), $quest->suspect ? $quest->suspect->id : null)->placeholder('Select a suspect')->class('form-control') }}
            </div>

            <div class="form-group">
                {{ html()->label('Location', 'location_id') }}
                {{ html()->select('location_id', $locations->pluck('name', 'id'), $quest->location ? $quest->location->id : null)->placeholder('Select a location')->class('form-control') }}
            </div>

            <div class="form-group">
                {{ html()->label('Quest Type', 'type') }}
                {{ html()->select('type', $quest->types)->placeholder('Select a type')->class('form-control') }}
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            <button type="button" class="btn btn-primary quest-type evidence-setup" data-toggle="modal" data-target="#importEvidenceRoomModal">
                Import From Previous Game
            </button>
        </div>
        <div class="col-sm-12 col-md-9">

            <div class="quest-type question-setup">
                @include('quest._question_form')
            </div>

            <div class="quest-type minigame-setup">
                @include('quest._minigame_form')
            </div>

        </div>
        {{ html()->closeModelForm() }}
    </div>
@stop