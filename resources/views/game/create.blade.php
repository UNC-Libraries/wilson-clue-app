@extends('layouts.admin', ['title' => 'Clue - '.$game->name])

@section('content')

    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.game.create',$game) !!}
            <div class="col-xs-12">
                <h1>Create a new game</h1>

                @include('admin._form_errors')

                {!! Form::model($game, ['route' => ['admin.game.store', $game->id]]) !!}
                    @include('game.input_groups.options')
                    <button type="submit" class="btn btn-primary">Save</button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@stop