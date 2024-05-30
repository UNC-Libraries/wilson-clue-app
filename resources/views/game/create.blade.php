@extends('layouts.admin', ['title' => 'Clue - '.$game->name])

@section('content')

    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.game.create',$game) !!}
            <div class="col-12">
                <h1>Create a new game</h1>

                @include('admin._form_errors')

                {{ html()->modelForm($game, 'POST', route('admin.game.store', [$game->id]))->open() }}
                    @include('game.input_groups.options')
                    <button type="submit" class="btn btn-primary">Save</button>
                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>

@stop