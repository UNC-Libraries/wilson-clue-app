@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.teams',$game) !!}
@stop

@section('game.content')

    @include('admin._alert')

    <div class="row">
        <div class="col-xs-12">
            <p class="lead text-center">Player Check-in</p>
        </div>
        <div class="col-xs-12">
            {!! Form::open(['route' => ['admin.game.checkin.player', $game->id]]) !!}
                <div class="form-group">
                    {!! Form::label('pid','Enter the player\'s PID') !!}
                    {!! Form::text('pid',null,['class'=>'form-control','autofocus']) !!}
                </div>
                <button type="submit" class="btn btn-primary">Check In</button>
            {!! Form::close() !!}
        </div>
    </div>

    @foreach($game->registeredTeams->sortBy('name')->chunk(3) as $chunk)
        <div class="row" style="margin-top: 20px;">
            @foreach($chunk as $team)
                <div class="col-xs-12 col-sm-4">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="font-size: 1.5em;" colspan="2">
                                    <a href="{{ route('admin.team.edit', $team->id) }}">
                                        {{ $team->name }}
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($team->players as $player)
                        <tr class="{{ $player->checked_in ? 'success' : '' }}">
                            <td><a href="{{ route('admin.player.edit', $player->id) }}">{{ $player->full_name }}</a></td>
                            <td>
                                @if(!$player->checked_in)
                                    {!! Form::open(['route' => ['admin.game.checkin.player', 'id' => $game->id, 'playerId' => $player->id]]) !!}
                                        <button type="submit"
                                                class="btn btn-success btn-sm">
                                            <span class="fa fa-check"></span>
                                        </button>
                                    {!! Form::close() !!}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach

@stop