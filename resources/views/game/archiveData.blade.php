@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.archive',$game) !!}
@stop

@section('game.content')

    <div class="row">
        <div class="col-12 col-xs-10 offset-sm-1">
            <p class="lead text-center">Edit the information that shows up on the public archive page</p>

            {!! Form::model($game, array('route'=> array('admin.game.update',$game->id), 'method'=>'PUT')) !!}
            <fieldset>
                <p>Show this game on the website?</p>
                <div class="radio">
                    <label>
                        {!! Form::radio('archive',1) !!}
                        Yes
                    </label>
                </div>
                <div class="form-check">
                    <label>
                        {!! Form::radio('archive',0) !!}
                        No
                    </label>
                </div>
            </fieldset>

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
                <span class="form-text">
                    The url for the image you want to show on the homepage. To retrieve a single image's URL from flickr.
                    <ol>
                        <li>Select the image from the flickr album.</li>
                        <li>Click on the <i class="fa fa-download" alt="Download, down arrow w/ bar underneath"></i> icon.</li>
                        <li>Click "View all sizes" in the pop-up menu.</li>
                        <li>Select the "Medium 500" Size.</li>
                        <li>Right-click on the image, and select "Copy Image address"</li>
                        <li>Paste the url in the input box above.</li>
                    </ol>
                </span>
            </div>
            <div class="form-group">
                {!! Form::label('special_thanks', 'Special Thanks Content') !!}
                {!! Form::textarea('special_thanks',null,array('class' => 'form-control', 'rows' => '10')) !!}
                <span class="form-text">Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</span>
            </div>
            <div class="form-group">
                {!! Form::label('team_accolades', 'Team Accolades Content') !!}
                {!! Form::textarea('team_accolades',null,array('class' => 'form-control', 'rows' => '10')) !!}
                <span class="form-text">Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</span>
            </div>


                <input type="submit" class="btn btn-lg btn-primary" value="Submit">

            {!! Form::close() !!}

        </div>
    </div>

@stop
