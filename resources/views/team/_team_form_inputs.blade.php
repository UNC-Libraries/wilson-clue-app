<!-- Team name -->
<div class="form-group col-12">
    {!! Form::label('name', 'Team Name', ['class' => 'fw-bold mb-1']) !!}
    {!! Form::text('name', null, array('class'=>'form-control')) !!}
</div>

<!-- Dietary -->
<div class="form-group col-12">
    {!! Form::label('dietary', 'Dietary Restrictions', ['class' => 'fw-bold mb-1']) !!}
    {!! Form::text('dietary', null, array('class'=>'form-control')) !!}
</div>

<div class="form-group col-12 text-end mt-3">
    <button type="submit" class="btn btn-primary">Save</button>
</div>