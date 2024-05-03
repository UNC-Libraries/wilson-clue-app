<div class="row">
    <!-- Name -->
    <div class="form-group col-12 col-sm-6">
        {!! Form::label('name', 'Name', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('name', null, array('class'=>'form-control')) !!}
    </div>
    <!-- Year -->
    <div class="form-group col-12 col-sm-6">
        {!! Form::label('year', 'Year', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('year', null, array('class'=>'form-control')) !!}
    </div>
</div>

<div class="row mt-2">
    <div class="form-group col-12">
        @include('partials._image_input',['current' => $minigameImage->src, 'alt' => $minigameImage->name])
    </div>
</div>

<div class="row">
    <div class="form-group col-12 text-end">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>