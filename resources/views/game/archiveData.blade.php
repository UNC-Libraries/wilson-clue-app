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
                {{ html()->label('Gallery/Album ID', 'flickr') }}
                {{ html()->text('flickr')->class('form-control') }}
            </div>
            <span class="form-text">
                    (PhotoShelter directions) The link for the Clue PhotoShelter gallery.
                    <ol>
                        <li>Select the gallery from the Clue games in PhotoShelter, https://unclibraries.photoshelter.com/galleries/C0000EdqU8HktBw8/Clue-Games</li>
                        <li>Past the gallery URL in the input box above. Your gallery url should be similar to this for the Spring 2024 game:
                            <span class="photoshelter-notes">https://unclibraries.photoshelter.com/galleries/C0000EdqU8HktBw8/G0000N8kjElu0Rx8/2024-04-Spring-Clue-Game</span></li>
                    </ol>
                </span>
            <div class="form-group">
                {{ html()->label('Start Image', 'flickr_start_img') }}
                {{ html()->text('flickr_start_img')->class('form-control') }}
                <span class="form-text">
                    (PhotoShelter directions) The url for the image for Photoshelter on the homepage. To retrieve a single image's URL from PhotoShelter.
                    <ol>
                        <li>Select the image from the PhotoShelter gallery.</li>
                        <li>Find the image id in the url. It will follow a format like this: <span class="photoshelter-notes">I0000Y7VrhVfzBIw</span></li>
                        <li>Paste the image id into the input box above and precede it with: <span class="photoshelter-notes">https://m.psecn.photoshelter.com/img-get2/</span></li>
                        <li>Your final url in the input box should look like this: <span class="photoshelter-notes">https://m.psecn.photoshelter.com/img-get2/I0000Y7VrhVfzBIw</span></li>
                    </ol>
                </span>
                <span class="form-text">
                    (Flickr directions) The url for the image you want to show on the homepage. To retrieve a single image's URL from flickr.
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
