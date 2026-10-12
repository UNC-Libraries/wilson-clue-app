@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.archive',$game) !!}
@stop

@section('game.content')

    <div class="row">
        <div class="col-12 col-sm-10 offset-sm-1">
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
                <div class="form-check spacing">
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
                <span class="form-text">
                    The link for the Clue Mediagraph gallery.
                    <ol>
                        <li>Select the gallery from the Clue games in Mediagraph, https://mediagraph.io/unclibrary/explore/collections/clue-at-wilson-library</li>
                        <li>Paste the gallery URL in the input box above. Your gallery url should be similar to this one for the Spring 2026 game:
                            <span class="photoshelter-notes">https://mediagraph.io/unclibrary/explore/collections/2026-04-spring-clue-game</span></li>
                    </ol>
                </span>
            </div>
            <div class="form-group">
                {{ html()->label('Start Image', 'flickr_start_img') }}
                {{ html()->text('flickr_start_img')->class('form-control') }}
                <span class="form-text">
                    The url for the image from Mediagraph on the homepage. To retrieve a single image's URL from Mediagraph.
                    <ol>
                        <li>Select the image from the Mediagraph gallery and click the gear menu.</li>
                        <li>From the gear menu, select "share"</li>
                        <li>Scroll to the bottom of the modal form that pops up and click the "Get Share Link" button</li>
                        <li>Copy the "Direct Link" URL and enter it above. Example URL: <span class="photoshelter-notes">https://dvnyn05pgqkzt.cloudfront.net/4288/492b76668e8d5891/82f218e5d286e77d/1946a0e2-41ee-498a-98e6-ea670c3d3e72.jpg</span></li>
                        <li>If you don't see a "Direct Link" option. You've selected more than one image and the link will
                        be to a slideshow and not the image you selected. You'll need to back out of the Mediagraph modal and
                        make sure only one image is selected.</li>
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
