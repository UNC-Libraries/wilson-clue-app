@extends('layouts.web', ['title' => 'Clue! Presented By Wilson Library'])

@section('content')
    <section class="main-section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="col-xs-12 subpage-banner">
                        <h1><a href="{{ route('web.index') }}">Clue</a> <small class="text-right">{{$game->start_time->format('F, jS Y')}}</small></h1>
                    </div>
                </div>
                <div class="col-xs-12 text-center">
                    <h2>Team Management</h2>
                    <p>
                        Use this page to add and remove players from your team, change your team name, and tell us about any dietary restrictions.
                    </p>
                </div>
                <div class="col-xs-12">
                    <h3>Team: {{ $team->name }}</h3>
                    <h4>
                        Status:
                        @if($team->waitlist)
                            <span class="text-red">
                                <span class="fa fa-warning"></span> Waitlist
                            </span>
                        @else
                            <span class="text-green">
                                <span class="fa fa-check-circle-o"></span> Enlisted
                            </span>
                        @endif
                    </h4>

                    <p>{{ $status_message }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="light-section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <h3>Add / Remove Players</h3>
                    @if(!$canRemove)
                        <p>
                            Currently you cannot remove any players, as this would unregister you from the game. If you need to remove a player,
                            add their replacement <em>first</em>, then remove the player.
                        </p>
                        <p>
                            If you won't have {{ $team::MINIMUM_PLAYERS }} players for the game, please contact us so we can find a replacement team.

                        </p>
                    @endif
                <hr>
                </div>
                <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
                    @if($errors->count() > 0)
                        <div class="alert alert-danger alert-dismissible text-left" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <p class="lead text-center">Uh-oh!</p>
                            <p>We had some trouble enlisting your team...</p>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ trans($error) }}</li>
                                @endforeach
                            </ul>
                            <p>If you continue to have problems, contact us at <a href="mailto:wilsonclue@listserv.unc.edu">wilsonclue@listserv.unc.edu</a></p>
                        </div>
                    @endif
                </div>
                <div class="col-xs-12 col-md-5 col-md-offset-1 col-lg-3 col-lg-offset-2">
                    <h4>Add a player</h4>
                    {!! Form::open(['route' => 'enlist.updateTeam.addPlayer']) !!}
                    <div class="form-group">
                        {!! Form::label('onyen','Onyen') !!}
                        {!! Form::text('onyen',null,['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-default btn-sm">Submit</button>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="col-xs-12 col-md-5 col-md-offset-1 col-lg-3 col-lg-offset-2">
                    <h4>Current Players</h4>
                    <ul class="list-unstyled current-players">
                        <li>
                            {{ $user->full_name }}
                        </li>
                        @foreach($team->players as $player)
                            @if($player->onyen == $user->onyen)
                                @continue
                            @endif
                            <li>
                                {{ $player->full_name }}
                                @if($canRemove)
                                    {!! Form::open(['route' => ['enlist.updateTeam.removePlayer','playerId' => $player->id],
                                                    'class' => 'form-inline']) !!}
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fa fa-trash"></i> Remove
                                    </button>
                                    {!! Form::close() !!}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="mid-section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
                    <h3>Edit your team information</h3>
                    @if(\Carbon\Carbon::now() < $team->game->start_time->subDays(3))
                        <div class="alert alert-warning">
                            <i class="fa fa-warning"></i> <small>Your team name and dietary restrictions must be finalized by {{ $team->game->start_time->subDays(3)->format('l, F jS @ g:i A') }}</small>
                        </div>
                        {!! Form::model($team, ['route' => 'enlist.updateTeam', 'id' => 'editTeamInfo']) !!}

                        <div class="form-group">
                            {!! Form::label('name','Team Name') !!}
                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('dietary', 'Dietary Restrictions') !!}
                            {!! Form:: textarea('dietary', null, ['class' => 'form-control', 'rows' => '4']) !!}
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                        {!! Form::close() !!}
                    @else
                        <div class="alert alert-warning">
                            <i class="fa fa-warning"></i> <small>
                                Team information cannot be edited at this time. If you need further assistance, contact us at
                                <a href="mailto:wilsonclue@listserv.unc.edu" class="alert-link">wilsonclue@listserv.unc.edu</a>
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@stop