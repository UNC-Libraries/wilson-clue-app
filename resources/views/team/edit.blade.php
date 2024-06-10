@extends('layouts.game', ['title' => 'Clue - '.$team->game->name])


@section('breadcrumb')
    {!! Breadcrumbs::render('admin.team.edit',$team) !!}
@stop

@section('game.content')

    <div class="row">
        <div class="col-12">
            @include('partials._delete_form', ['route' => ['admin.team.destroy', $team->id]])
            <h2>{{ $team->name }} <small>{{ $team->waitlist ? 'Waitlist' : 'Registered' }}</small></h2>

            @include('admin._form_errors')

            <div class="row">
                <div class="col-12 col-xs-8 offset-sm-2">
                    {{ html()->modelForm($team, 'PUT', route('admin.team.update', [$team->id]))->open() }}
                    @include('team._team_form_inputs')
                    {{ html()->closeModelForm() }}
                </div>
            </div>

            <h3>Players</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teamEmailsModal">
                <i class="fa fa-envelope-o"></i>
                Emails
            </button>
            @if($team->players->count() < 5)
                <div class="row">
                    <div class="col-3 offset-xs-9 col-xs-2 offset-sm-10 text-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPlayerModal"><span class="fa fa-plus-circle"></span> Add New</button>
                    </div>
                </div>
            @endif

            <table class="table player-table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Onyen</th>
                    <th>Email</th>
                    <th>PID</th>
                    <th>Class</th>
                    <th>Academic Group</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($team->players as $player)
                    <tr>
                        <td><a href="{{ route('admin.player.edit',$player->id) }}">{{ $player->full_name }}</a></td>
                        <td>{{ $player->onyen }}</td>
                        <td>{{ $player->email }}</td>
                        <td>{{ $player->pid }}</td>
                        <td>{{ $player->class }}</td>
                        <td>{{ $player->academic_group }}</td>
                        <td>
                            @include('partials._delete_form', [
                                'route' => ['admin.team.removePlayer',$team->id, $player->id],
                                'message' => 'Are you sure you want to remove '.$player->full_name.' from '.$team->name.'?',
                            ])
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('modal')
    @include('partials._player_email_modal',['teams' => [$team], 'modal_id' => 'teamEmails'])

    <!-- Add player modal -->
    <div class="modal fade" id="addPlayerModal" tabindex="-1" role="dialog" aria-labelledby="addPlayerModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <h4 class="modal-title" id="addPlayerModalLabel">Add player to {{ $team->name }}</h4>
                </div>
                <div class="modal-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#onyen" aria-controls="onyen" role="tab" data-toggle="tab">Via Onyen</a></li>
                        <li role="presentation"><a href="#noOnyen" aria-controls="noOnyen" role="tab" data-toggle="tab">No Onyen</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="onyen">

                        {{ html()->form('POST', route('admin.team.addPlayer', ['id' => $team->id]))->open() }}
                        <!-- Onyen -->
                            <div class="form-group col-12">
                                {{ html()->label('Onyen', 'onyen') }}
                                {{ html()->text('onyen')->class('form-control') }}
                            </div>
                            @if($team->game->students_only)
                                <div class="form-group col-12">
                                    <label for="override_non_student">
                                        <input type="checkbox" id="override_non_student" class="form-check-input" name="override_non_student" value="1"> Override Non-Student?
                                    </label>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-primary">Submit</button>
                        {{ html()->form()->close() }}

                        </div>
                        <div role="tabpanel" class="tab-pane" id="noOnyen">
                            <p class="text-warning">Note: Player will need to login using their email and password.</p>

                        {{ html()->form('POST', route('admin.team.addPlayer', ['id' => $team->id]))->open() }}

                        <!-- Email -->
                            <div class="form-group col-12 col-xs-6">
                                {{ html()->label('Email', 'email') }}
                                {{ html()->text('email')->class('form-control')->required() }}
                            </div>

                            <!-- Password -->
                            <div class="form-group col-12 col-xs-6">
                                {{ html()->label('Password', 'password') }}
                                {{ html()->text('password')->class('form-control')->required() }}
                            </div>

                            <!-- First Name -->
                            <div class="form-group col-12 col-xs-6">
                                {{ html()->label('First Name', 'first_name') }}
                                {{ html()->text('first_name')->class('form-control')->required() }}
                            </div>
                            <!-- Last Name -->
                            <div class="form-group col-12 col-xs-6">
                                {{ html()->label('Last Name', 'last_name') }}
                                {{ html()->text('last_name')->class('form-control')->required() }}
                            </div>

                            <!-- Class -->
                            <div class="form-group col-12 col-xs-6">
                                {{ html()->label('Class', 'class_code') }}
                                {{ html()->select('class_code', $class_options, 'new')->class('form-control')->placeholder('Select a Class') }}
                            </div>
                            <!-- Academic Code -->
                            <div class="form-group col-12 col-xs-6">
                                {{ html()->label('Academic Group', 'academic_group_code') }}
                                {{ html()->select('academic_group_code', $academic_group_options, 'new')->class('form-control')->placeholder('Select a Class') }}
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            {{ html()->form()->close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop