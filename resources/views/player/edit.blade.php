@extends('layouts.asset_edit', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'player',
    'model' => $player,
    'delete_message' => 'Are your sure you want to delete '.$player->full_name.'?',
    'page_title' => 'Edit '.$player->full_name,
])

@section('model_edit_inputs')
<div class="row">
    @if($player->manual)
        <div class="col-xs-12">
            <span class="text-warning">
                <span class="fa fa-warning"></span>
                Manually Entered
            </span>
        </div>
    @endif
    <div class="col-xs-12">
        <h2>Teams</h2>
        <ul>
            @foreach($player->teams as $team)
                <li><a href="{{ route('admin.team.edit',['id' => $team->id]) }}">{{ $team->name }} ({{ $team->game->name }})</a></li>
            @endforeach
        </ul>
    </div>
    <div class="col-xs-12">
        <h2>Player Info</h2>
        {!! Form::model($player, array('route'=> ["admin.player.update",$player->id], 'method'=>'PUT')) !!}
            @include('player._inputs')
        {!! Form::close() !!}
    </div>
</div>
@stop