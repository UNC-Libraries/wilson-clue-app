<!-- Team name -->
<div class="form-group col-12">
    {{ html()->label('Team Name', 'name')->class('fw-bold mb-1')  }}
    {{ html()->text('name')->class('form-control') }}
</div>

<!-- Dietary -->
<div class="form-group col-12">
    {{ html()->label('Dietary Restrictions', 'dietary')->class('fw-bold mb-1') }}
    {{ html()->text('dietary')->class('form-control') }}
</div>

<div class="form-group col-12 text-end mt-3">
    <button type="submit" class="btn btn-primary">Save</button>
</div>