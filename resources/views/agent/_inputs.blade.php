<div class="row">
    <!-- Onyen -->
    <div class="form-group col col-sm-6">
        {{ html()->label('Onyen', 'onyen')->class('fw-bold mb-1') }}
        {{ html()->text('onyen')->class('form-control') }}
    </div>

    <!-- Email -->
    <div class="form-group col col-sm-6">
        {{ html()->label('Email', 'email')->class('fw-bold mb-1') }}
        {{ html()->text('email')->class('form-control') }}
    </div>
</div>

<div class="row mt-3">
    <!-- Onyen -->
    <div class="form-group col-12 col-sm-6">
        {{ html()->label('First Name', 'first_name')->class('fw-bold mb-1') }}
        {{ html()->text('first_name')->class('form-control') }}
    </div>

    <!-- Email -->
    <div class="form-group col-12 col-sm-6">
        {{ html()->label('Last Name', 'last_name')->class('fw-bold mb-1') }}
        {{ html()->text('last_name')->class('form-control') }}
    </div>
</div>
<?php print_r($agent); ?>
<div class="row mt-3">
    <!-- Retired -->
    <div class="form-group col-12 col-sm-4">
        <div class="form-check">
            <label>
                {{ html()->checkbox('retired', isset($agent->retired)) }} Retired?
            </label>
        </div>
    </div>
    <!-- Display on web -->
    <div class="form-group col-12 col-sm-4">
        <div class="form-check">
            <label>
                {{ html()->checkbox('web_display', isset($agent->web_display)) }} Display on Website?
            </label>
        </div>
    </div>
    <!-- Admin -->
    <div class="form-group col-12 col-sm-4">
        <div class="form-check">
            <label>
                {{ html()->checkbox('admin', isset($agent->admin)) }} Admin?
            </label>
        </div>
    </div>
</div>

<div class="row mt-3">
    <!-- Agent Title -->
    <div class="form-group col-12 col-sm-4">
        {{ html()->label('Agent Title', 'title')->class('fw-bold mb-1') }}
        {{ html()->text('title')->class('form-control') }}
    </div>

    <!-- Location -->
    <div class="form-group col-12 col-sm-4">
        {{ html()->label('Location', 'location')->class('fw-bold mb-1') }}
        {{ html()->text('location')->class('form-control') }}
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