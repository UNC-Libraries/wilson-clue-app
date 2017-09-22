<div class="row">
    <!-- Onyen -->
    <div class="form-group col-xs-12 col-sm-6">
        {!! Form::label('onyen', 'Onyen') !!}
        {!! Form::text('onyen', null, array('class'=>'form-control')) !!}
    </div>

    <!-- Email -->
    <div class="form-group col-xs-12 col-sm-6">
        {!! Form::label('email', 'Email') !!}
        {!! Form::text('email', null, array('class'=>'form-control')) !!}
    </div>
</div>

<div class="row">
    <!-- Onyen -->
    <div class="form-group col-xs-12 col-sm-6">
        {!! Form::label('first_name', 'First Name') !!}
        {!! Form::text('first_name', null, array('class'=>'form-control')) !!}
    </div>

    <!-- Email -->
    <div class="form-group col-xs-12 col-sm-6">
        {!! Form::label('last_name', 'Last Name') !!}
        {!! Form::text('last_name', null, array('class'=>'form-control')) !!}
    </div>
</div>

<div class="row">
    <!-- Retired -->
    <div class="form-group col-xs-12 col-sm-4">
        <div class="checkbox">
            <label>
                {!! Form::checkbox('retired') !!} Retired?
            </label>
        </div>
    </div>
    <!-- Display on web -->
    <div class="form-group col-xs-12 col-sm-4">
        <div class="checkbox">
            <label>
                {!! Form::checkbox('web_display') !!} Display on Website?
            </label>
        </div>
    </div>
    <!-- Admin -->
    <div class="form-group col-xs-12 col-sm-4">
        <div class="checkbox">
            <label>
                {!! Form::checkbox('admin') !!} Admin?
            </label>
        </div>
    </div>
</div>

<div class="row">
    <!-- Agent Title -->
    <div class="form-group col-xs-12 col-sm-4">
        {!! Form::label('title', 'Agent Title') !!}
        {!! Form::text('title', null, array('class'=>'form-control')) !!}
    </div>

    <!-- Location -->
    <div class="form-group col-xs-12 col-sm-4">
        {!! Form::label('location', 'Location') !!}
        {!! Form::text('location', null, array('class'=>'form-control')) !!}
    </div>
</div>

<div class="row">
    <div class="form-group col-xs-12">
        @include('partials._image_input',['current' => $agent->src, 'alt' => $agent->full_name])
    </div>
</div>

<div class="row">
    <div class="form-group col-xs-12 text-right">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>