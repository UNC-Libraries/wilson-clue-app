@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.archive',$game) !!}
@stop

@section('game.content')

    <div class="row">
        <div class="col-12 col-xs-10 offset-sm-1">
            <p class="lead text-center">Edit the information that shows up on the public archive page</p>

            {{ html()->modelForm($game, 'PUT', route('admin.game.update', [$game->id]))->open() }}
            <fieldset>
                <p>Show this game on the website?</p>
                <div class="radio">
                    <label>
                        {{ html()->radio('archive', false, 1) }}
                        Yes
                    </label>
                </div>
                <div class="form-check">
                    <label>
                        {{ html()->radio('archive', false, 0) }}
                        No
                    </label>
                </div>
            </fieldset>

            <div class="form-group">
                {{ html()->label('Winning Team', 'winning_team') }}
                {{ html()->select('winning_team', $game->registeredTeams->pluck('name', 'id'), $game->winningTeam ? $game->winningTeam->id : null)->placeholder('Select a winner')->class('form-control') }}
            </div>
            <div class="form-group">
                {{ html()->label('Flickr Album ID', 'flickr') }}
                {{ html()->text('flickr')->class('form-control') }}
            </div>
            <div class="form-group">
                {{ html()->label('Flickr Start Image', 'flickr_start_img') }}
                {{ html()->text('flickr_start_img')->class('form-control') }}
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
                {{ html()->label('Special Thanks Content', 'special_thanks') }}
                {{ html()->textarea('special_thanks')->class('form-control')->rows('10') }}
                <span class="form-text">Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</span>
            </div>
            <div class="form-group">
                {{ html()->label('Team Accolades Content', 'team_accolades') }}
                {{ html()->textarea('team_accolades')->class('form-control')->rows('10') }}
                <span class="form-text">Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</span>
            </div>
                <input type="submit" class="btn btn-lg btn-primary" value="Submit">

            {{ html()->closeModelForm() }}

        </div>
    </div>

@stop
