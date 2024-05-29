<div class="row">
    <div class="col-xs-12">
        <legend>Image</legend>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-8">
        <div class="row">
            <div class="col-xs-12">
                <div id="imageFileInput" class="form-group">
                    {{ html()->label('Select a file', 'new_image_file') }}
                    {{ html()->file('new_image_file')->class('form-control') }}
                </div>
                <span class="help-block">
                    Files must be smaller than <code>512kb</code>, and be of one of the following filetypes:
                    <code>jpg</code>, <code>jpeg</code>, <code>png</code>, <code>svg</code>
                </span>
            </div>
        </div>
    </div>
    @if($current)
        <div class="col-xs-12 col-sm-6 col-md-2">
            {!! Html::image($current,$alt, ['class'=>'img-responsive img-thumbnail']) !!}
        </div>
    @endif
</div>