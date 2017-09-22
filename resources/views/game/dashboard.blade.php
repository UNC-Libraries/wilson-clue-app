@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.show',$game) !!}
    @include('admin._alert')
@stop


@section('game.content')
    @include('admin._form_errors')
    @include('game._warnings')
    <div class="row">

        @if(!$game->in_progress)
            @include('game.snapshots.registration')
        @endif

        <!-- App Alerts -->
        <div class="col-xs-12">
            <div class="dash-section">
                <div class="dash-section-header">
                    <h3>Send In-game Alert to Players</h3>
                </div>
                <div class="dash-section-body">
                    {!! Form::open(['route' => ['admin.game.alert.store', $game->id]]) !!}
                        <div class="form-group">
                            {!! Form::label('message', 'Alert') !!}
                            {!! Form::text('message',null,['class'=>'form-control']) !!}
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><span class="fa fa-send"></span> Send</button>
                    {!! Form::close() !!}
                    <h4>Sent alerts</h4>
                    <div class="watcher" data-url="{{ route('admin.game.glados.alerts', $game->id) }}"></div>
                </div>
            </div>
        </div>

        <!-- Glados -->
        <div class="col-xs-12">
            <div class="dash-section">
                <div class="dash-section-header">
                    <h3>Status</h3>
                </div>
                <div class="dash-section-body watcher" data-url="{{ route('admin.game.glados.status', $game->id) }}"></div>
            </div>
        </div>

        <!-- Viewing -->
        <div class="col-xs-12">
            <div class="dash-section">
                <div class="dash-section-header">
                    <h3>Viewing</h3>
                </div>
                <div class="dash-section-body watcher" data-url="{{ route('admin.game.glados.viewing', $game->id) }}"></div>
            </div>
        </div>

        <!-- UnJudged Answers -->
        <div class="col-xs-12">
            <div class="dash-section">
                <div class="dash-section-header">
                    <h3>Unjudged Answers</h3>
                </div>
                <div class="dash-section-body watcher" data-url="{{ route('admin.game.glados.judgement', $game->id) }}"></div>
            </div>
        </div>
    </div>

    @if($game->in_progress)
        @include('game.snapshots.registration')
    @endif
@stop