<div class="row">
    <!-- Onyen -->
    <div class="form-group col-xs-12 col-sm-6">
        {{ html()->label('Onyen', 'onyen') }}
        {{ html()->text('onyen')->class('form-control') }}
    </div>

    <!-- Email -->
    <div class="form-group col-xs-12 col-sm-6">
        {{ html()->label('Email', 'email') }}
        {{ html()->text('email')->class('form-control') }}
    </div>
</div>

<div class="row">
    <!-- Onyen -->
    <div class="form-group col-xs-12 col-sm-6">
        {{ html()->label('First Name', 'first_name') }}
        {{ html()->text('first_name')->class('form-control') }}
    </div>

    <!-- Email -->
    <div class="form-group col-xs-12 col-sm-6">
        {{ html()->label('Last Name', 'last_name') }}
        {{ html()->text('last_name')->class('form-control') }}
    </div>
</div>

<div class="row">
    <!-- Retired -->
    <div class="form-group col-xs-12 col-sm-4">
        <div class="checkbox">
            <label>
                {{ html()->checkbox('retired', false) }} Retired?
            </label>
        </div>
    </div>
    <!-- Display on web -->
    <div class="form-group col-xs-12 col-sm-4">
        <div class="checkbox">
            <label>
                {{ html()->checkbox('web_display', false) }} Display on Website?
            </label>
        </div>
    </div>
    <!-- Admin -->
    <div class="form-group col-xs-12 col-sm-4">
        <div class="checkbox">
            <label>
                {{ html()->checkbox('admin', false) }} Admin?
            </label>
        </div>
    </div>
</div>

<div class="row">
    <!-- Agent Title -->
    <div class="form-group col-xs-12 col-sm-4">
        {{ html()->label('Agent Title', 'title') }}
        {{ html()->text('title')->class('form-control') }}
    </div>

    <!-- Location -->
    <div class="form-group col-xs-12 col-sm-4">
        {{ html()->label('Location', 'location') }}
        {{ html()->text('location')->class('form-control') }}
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