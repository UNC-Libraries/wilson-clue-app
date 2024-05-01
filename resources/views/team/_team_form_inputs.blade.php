<!-- Team name -->
<div class="form-group col-12">
    {!! Form::label('name', 'Team Name') !!}
    {!! Form::text('name', null, array('class'=>'form-control')) !!}
</div>

<!-- Dietary -->
<div class="form-group col-12">
    {!! Form::label('dietary', 'Dietary Restrictions') !!}
    {!! Form::text('dietary', null, array('class'=>'form-control')) !!}
</div>

<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-primary">Save</button>
</div>