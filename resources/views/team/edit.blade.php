@extends('layouts.game', ['title' => 'Clue - '.$team->game->name])


@section('breadcrumb')
    {!! Breadcrumbs::render('admin.team.edit',$team) !!}
@stop

@section('game.content')

    <div class="row">
        <div class="col-xs-12">
            @include('partials._delete_form', ['route' => ['admin.team.destroy', $team->id]])
            <h2>{{ $team->name }} <small>{{ $team->waitlist ? 'Waitlist' : 'Registered' }}</small></h2>

            @include('admin._form_errors')

            <div class="row">
                <div class="col-xs-12 col-sm-8 col-sm-offset-2">
                    {!! Form::model($team, ['route' => ['admin.team.update', $team->id], 'method' => 'PUT']) !!}
                    @include('team._team_form_inputs')
                    {!! Form::close() !!}
                </div>
            </div>

            <h3>Players</h3>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#teamEmailsModal">
                <i class="fa fa-envelope-o"></i>
                Emails
            </button>
            @if($team->players->count() < 5)
                <div class="row">
                    <div class="col-xs-3 col-xs-offset-9 col-sm-2 col-sm-offset-10 text-right">
                        <button class="btn btn-success" data-toggle="modal" data-target="#addPlayerModal"><span class="fa fa-plus-circle"></span> Add New</button>
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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

                        {!! Form::open(['route' => ['admin.team.addPlayer', 'id' => $team->id]]) !!}
                        <!-- Onyen -->
                            <div class="form-group col-xs-12">
                                {!! Form::label('onyen', 'Onyen') !!}
                                {!! Form::text('onyen', null, array('class'=>'form-control')) !!}
                            </div>
                            @if($team->game->students_only)
                                <div class="form-group col-xs-12">
                                    <label for="override_non_student">
                                        <input type="checkbox" id="override_non_student" name="override_non_student" value="1"> Override Non-Student?
                                    </label>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-primary">Submit</button>
                        {!! Form::close() !!}

                        </div>
                        <div role="tabpanel" class="tab-pane" id="noOnyen">
                            <p class="text-warning">Note: Player will need to login using their email and password.</p>

                        {!! Form::open(['route' => ['admin.team.addPlayer', 'id' => $team->id]]) !!}

                        <!-- Email -->
                            <div class="form-group col-xs-12 col-sm-6">
                                {!! Form::label('email', 'Email') !!}
                                {!! Form::text('email', null, array('class'=>'form-control', 'required')) !!}
                            </div>

                            <!-- Password -->
                            <div class="form-group col-xs-12 col-sm-6">
                                {!! Form::label('password', 'Password') !!}
                                {!! Form::text('password', null, array('class'=>'form-control', 'required')) !!}
                            </div>

                            <!-- First Name -->
                            <div class="form-group col-xs-12 col-sm-6">
                                {!! Form::label('first_name', 'First Name') !!}
                                {!! Form::text('first_name', null, array('class'=>'form-control', 'required')) !!}
                            </div>
                            <!-- Last Name -->
                            <div class="form-group col-xs-12 col-sm-6">
                                {!! Form::label('last_name', 'Last Name') !!}
                                {!! Form::text('last_name', null, array('class'=>'form-control', 'required')) !!}
                            </div>

                            <!-- Class -->
                            <div class="form-group col-xs-12 col-sm-6">
                                {!! Form::label('class_code', 'Class') !!}
                                {!! Form::select('class_code', $class_options, 'new', ['class'=>'form-control', 'placeholder' => 'Select a Class']) !!}
                            </div>
                            <!-- Academic Code -->
                            <div class="form-group col-xs-12 col-sm-6">
                                {!! Form::label('academic_group_code', 'Academic Group') !!}
                                {!! Form::select('academic_group_code', $academic_group_options, 'new', ['class'=>'form-control', 'placeholder' => 'Select a Class']) !!}
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop