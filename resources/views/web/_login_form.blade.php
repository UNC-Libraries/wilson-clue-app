<div class="login-div" style="height: 96vh;">
    <div class="login-form" style="position: absolute; top:0; left:0; right:0; bottom: 0; margin:auto; height: 320px; width:320px;">

        <div style="margin-bottom: 1em">
            <p class="text-center text-warning">
                @if ($errors->has('failedLogin'))
                    {!! $errors->first('failedLogin') !!}
                @endif
                &nbsp;
            </p>
        </div>


    @if(Route::currentRouteName() == 'admin.login.form')
        {!! Form::open(['route' => 'admin.login']) !!}
        <legend class="text-danger">Admin Login Form</legend>
    @elseif(Route::currentRouteName() == 'player.login.form')
        {!! Form::open(['route' => 'player.login']) !!}
    @endif

    <div class="form-group">
        {!! Form::label('onyen', 'Onyen') !!}
        {!! Form::text('onyen', null, ['class'=>'form-control', 'placeholder'=>'onyen']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('password', 'Password') !!}
        {!! Form::password('password', ['class'=>'form-control', 'placeholder'=>'password']) !!}
    </div>

    <button type="submit" class="btn btn-primary">Login</button>

    {!! Form::close() !!}

    </div>

</div>