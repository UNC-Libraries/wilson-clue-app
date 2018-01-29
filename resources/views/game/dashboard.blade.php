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
                    <ul class="list-unstyled">
                        @foreach($game->alerts as $alert)
                            <li>
                                <em>{{ $alert->message }}</em>
                                <small>
                                    @include('partials._delete_form', ['route' => ['admin.game.alert.destroy', $game->id, $alert->id], 'class' => 'btn-xs'])
                                </small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Glados -->
        <div class="col-xs-12">
            <div class="dash-section">
                <div class="dash-section-header">
                    <h3>
                        Status
                        <button class="btn btn-default refresh-content" data-url="{{ route('admin.game.glados.status', $game->id) }}" data-target="#gladosStatus"><i class="fa fa-refresh"></i></button>
                    </h3>
                </div>
                <div class="dash-section-body" id="gladosStatus"></div>
            </div>
        </div>

        <!-- Viewing -->
        <div class="col-xs-12">
            <div class="dash-section">
                <div class="dash-section-header">
                    <h3>
                        Viewing
                        <button class="btn btn-default refresh-content" data-url="{{ route('admin.game.glados.viewing', $game->id) }}" data-target="#gladosViewing"><i class="fa fa-refresh"></i></button>
                    </h3>
                </div>
                <div class="dash-section-body" id="gladosViewing"></div>
            </div>
        </div>
    </div>

    @if($game->in_progress)
        @include('game.snapshots.registration')
    @endif
@stop