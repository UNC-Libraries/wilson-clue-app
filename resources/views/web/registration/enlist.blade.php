@extends('layouts.web', ['title' => 'Clue! Presented By Wilson Library'])

@section('content')

<section class="main-section">
    <div class="container">
        <div class="row">
            <div class="col-12 subpage-banner">
                <h1><a href="{{ route('web.index') }}">Clue</a> <small class="text-right">{{$game->start_time->format('F, jS Y')}}</small></h1>
            </div>
            <div class="col-12 col-sm-10 offset-md-1 col-md-8 offset-lg-2 text-center">
                <h2>Enlistment Form</h2>
                @if($game->spots_left == 0)
                    <div class="alert alert-danger">
                        <p class="lead">Notice!</p>
                        <p>Unfortunately, we've run out of space. But don't loose hope! Sign up on the waitlist, and if spots open up, we'll contact you ASAP.</p>
                    </div>
                @endif
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

            <div class="col-12 col-sm-8 offset-md-2 enlistment-form">
                <p>To the SIA,</p>
                <p>I would like to enlist my team for the Clue investigation scheduled on {{ $game->start_time->format('l, \t\h\e jS \o\f F, Y') }}.</p>
                @if($game->students_only)
                    <p>By enlisting, I certify that my team members and I are all UNC - Chapel Hill Students and that none of us have participated in a previous investigation.</p>
                @else
                    <p>By enlisting, I certify that my team members and I have not participated in a previous investigation.</p>
                @endif
                {!! Form::open(['route' => 'enlist.submit']) !!}
                    <div class="form-group">
                        {!! Form::label('teamName', 'Team Name') !!}
                        {!! Form::text('teamName',null,['class'=>'form-control', 'required', 'autofocus']) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('onyen', 'Onyen') !!}
                        {!! Form::text('onyen',null,['class'=>'form-control', 'required']) !!}
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-lg btn-primary">Enlist!</button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</section>

@stop