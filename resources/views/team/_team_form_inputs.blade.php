<!-- Team name -->
<div class="form-group col-xs-12">
    {{ html()->label('Team Name', 'name') }}
    {{ html()->text('name')->class('form-control') }}
</div>

<!-- Dietary -->
<div class="form-group col-xs-12">
    {{ html()->label('Dietary Restrictions', 'dietary') }}
    {{ html()->text('dietary')->class('form-control') }}
</div>

<div class="form-group col-xs-12 text-right">
    <button type="submit" class="btn btn-primary">Save</button>
</div>