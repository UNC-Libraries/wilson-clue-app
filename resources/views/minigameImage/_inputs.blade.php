<div class="row">
    <!-- Name -->
    <div class="form-group col-xs-12 col-sm-6">
        {{ html()->label('Name', 'name') }}
        {{ html()->text('name')->class('form-control') }}
    </div>
    <!-- Year -->
    <div class="form-group col-xs-12 col-sm-6">
        {{ html()->label('Year', 'year') }}
        {{ html()->text('year')->class('form-control') }}
    </div>
</div>

<div class="row">
    <div class="form-group col-xs-12">
        @include('partials._image_input',['current' => $minigameImage->src, 'alt' => $minigameImage->name])
    </div>
</div>

<div class="row">
    <div class="form-group col-xs-12 text-right">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>