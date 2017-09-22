@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.archive',$game) !!}
@stop

@section('game.content')

    <div class="row">
        <div class="col-xs-12 col-sm-10 col-sm-offset-1">
            <p class="lead text-center">Edit the information that shows up on the public archive page</p>

            {!! Form::model($game, array('route'=> array('admin.game.update',$game->id), 'method'=>'PUT')) !!}
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('archive') !!} Show this game on the website?
                    </label>
                </div>

            <div class="form-group">
                {!! Form::label('winning_team','Winning Team') !!}
                {!! Form::select('winning_team',$game->registeredTeams->pluck('name','id'), $game->winningTeam ? $game->winningTeam->id : null, array('placeholder' => 'Select a winner', 'class' => 'form-control')) !!}
            </div>
            <div class="form-group">
                {!! Form::label('flickr', 'Flickr Album ID') !!}
                {!! Form::text('flickr',null,array('class' => 'form-control')) !!}
            </div>
            <div class="form-group">
                {!! Form::label('flickr_start_img', 'Flickr Start Image') !!}
                {!! Form::text('flickr_start_img',null,array('class' => 'form-control')) !!}
                <span class="help-block">The url for the image you want to show while the album loads</span>
            </div>
            <div class="form-group">
                {!! Form::label('special_thanks', 'Special Thanks Content') !!}
                {!! Form::textarea('special_thanks',null,array('class' => 'form-control', 'rows' => '10')) !!}
                <span class="help-block">Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</span>
            </div>
            <div class="form-group">
                {!! Form::label('team_accolades', 'Team Accolades Content') !!}
                {!! Form::textarea('team_accolades',null,array('class' => 'form-control', 'rows' => '10')) !!}
                <span class="help-block">Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</span>
            </div>


                <input type="submit" class="btn btn-lg btn-primary" value="Submit">

            {!! Form::close() !!}

        </div>
    </div>

@stop
