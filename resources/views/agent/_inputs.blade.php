<div class="row">
    <!-- Onyen -->
    <div class="form-group col col-sm-6">
        {!! Form::label('onyen', 'Onyen', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('onyen', null, array('class'=>'form-control')) !!}
    </div>

    <!-- Email -->
    <div class="form-group col col-sm-6">
        {!! Form::label('email', 'Email', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('email', null, array('class'=>'form-control')) !!}
    </div>
</div>

<div class="row mt-3">
    <!-- Onyen -->
    <div class="form-group col-12 col-sm-6">
        {!! Form::label('first_name', 'First Name', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('first_name', null, array('class'=>'form-control')) !!}
    </div>

    <!-- Email -->
    <div class="form-group col-12 col-sm-6">
        {!! Form::label('last_name', 'Last Name', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('last_name', null, array('class'=>'form-control')) !!}
    </div>
</div>

<div class="row mt-3">
    <!-- Retired -->
    <div class="form-group col-12 col-sm-4">
        <div class="form-check">
            <label>
                {!! Form::checkbox('retired') !!} Retired?
            </label>
        </div>
    </div>
    <!-- Display on web -->
    <div class="form-group col-12 col-sm-4">
        <div class="form-check">
            <label>
                {!! Form::checkbox('web_display') !!} Display on Website?
            </label>
        </div>
    </div>
    <!-- Admin -->
    <div class="form-group col-12 col-sm-4">
        <div class="form-check">
            <label>
                {!! Form::checkbox('admin') !!} Admin?
            </label>
        </div>
    </div>
</div>

<div class="row mt-3">
    <!-- Agent Title -->
    <div class="form-group col-12 col-sm-4">
        {!! Form::label('title', 'Agent Title', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('title', null, array('class'=>'form-control')) !!}
    </div>

    <!-- Location -->
    <div class="form-group col-12 col-sm-4">
        {!! Form::label('location', 'Location', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('location', null, array('class'=>'form-control')) !!}
    </div>
</div>

<div class="row mt-2">
    <div class="form-group col-12">
        @include('partials._image_input',['current' => $agent->src, 'alt' => $agent->full_name])
    </div>
</div>

<div class="row">
    <div class="form-group col-12 text-end">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>