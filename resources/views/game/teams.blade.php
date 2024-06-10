@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.teams',$game) !!}
@stop

@section('game.content')

    @include('admin._alert')
    <div class="row">
        <div class="col-12">
            <p class="lead text-center">
                Add & Remove Teams, Manage waitlist
            </p>
        </div>
        <div class="col-md-3 offset-xs-9 col-xs-2 offset-md-9 text-end">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTeamModal"><span class="fa fa-plus-circle"></span> Add New</button>
        </div>
        <div class="col-12">
            <h2>Registered <small>{{ $game->registeredTeams->count()}} {{ str_plural('team',$game->registeredTeams->count()) }}</small></h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registeredPlayerEmailsModal">
                <i class="fa fa-envelope-o"></i>
                Registered Emails
            </button>
            @include('game._team_table',['teams'=>$game->registeredTeams])
        </div>
        <div class="col-12">
            <h2>Waitlist <small>{{ $game->waitlistTeams->count()}} {{ str_plural('team',$game->waitlistTeams->count()) }}</small></h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#waitlistPlayerEmailsModal">
                <i class="fa fa-envelope-o"></i>
                Waitlist Emails
            </button>
            @include('game._team_table',['teams'=>$game->waitlistTeams])
        </div>
    </div>

@stop

@section('modal')
    <!-- Add team modal -->
    <div class="modal fade" id="addTeamModal" tabindex="-1" role="dialog" aria-labelledby="addTeamModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="addPlayerModalLabel">Add team to {{ $game->name }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            {{ html()->form('POST', route('admin.game.addTeam', [$game->id]))->open() }}
                            @include('team._team_form_inputs')
                            {{ html()->form()->close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials._player_email_modal',['teams' => $game->registeredTeams, 'modal_id' => 'registeredPlayerEmails'])
    @include('partials._player_email_modal',['teams' => $game->waitlistTeams, 'modal_id' => 'waitlistPlayerEmails'])
@stop