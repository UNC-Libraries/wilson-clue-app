<div class="row">
    <!-- Name -->
    <div class="form-group col-12 col-xs-6">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', null, array('class'=>'form-control')) !!}
    </div>
    <!-- Year -->
    <div class="form-group col-12 col-xs-6">
        {!! Form::label('year', 'Year') !!}
        {!! Form::text('year', null, array('class'=>'form-control')) !!}
    </div>
</div>

<div class="row">
    <div class="form-group col-12">
        @include('partials._image_input',['current' => $minigameImage->src, 'alt' => $minigameImage->name])
    </div>
</div>

<div class="row">
    <div class="form-group col-12 text-right">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>