<div class="row mt-3">
    <div class="col-12">
        <legend>Image</legend>
    </div>
    <div class="col-12 col-sm-6 col-md-8">
        <div class="row">
            <div class="col-12">
                <div id="imageFileInput" class="form-group">
                    {{ html()->label('Select a file', 'new_image_file')->class('fw-bold') }}
                    {{ html()->file('new_image_file')->class('form-control') }}
                </div>
                <span class="form-text">
                    Files must be smaller than <code>1024kb</code>, and be of one of the following filetypes:
                    <code>jpg</code>, <code>jpeg</code>, <code>png</code>, <code>svg</code>
                </span>
            </div>
        </div>
    </div>
    @if($current)
        <div class="col-12 col-sm-6 col-md-2">
            {{ html()->img(asset($current), $alt)->class('img-responsive img-thumbnail') }}
        </div>
    @endif
</div>