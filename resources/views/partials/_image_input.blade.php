<div class="row">
    <div class="col-12">
        <legend>Image</legend>
    </div>
    <div class="col-12 col-xs-6 col-sm-8">
        <div class="row">
            <div class="col-12">
                <div id="imageFileInput" class="form-group">
                    {!! Form::label('new_image_file','Select a file') !!}
                    {!! Form::file('new_image_file',['class'=>'form-control']) !!}
                </div>
                <span class="form-text">
                    Files must be smaller than <code>512kb</code>, and be of one of the following filetypes:
                    <code>jpg</code>, <code>jpeg</code>, <code>png</code>, <code>svg</code>
                </span>
            </div>
        </div>
    </div>
    @if($current)
        <div class="col-12 col-xs-6 col-sm-2">
            {!! Html::image($current,$alt, ['class'=>'img-fluid img-thumbnail']) !!}
        </div>
    @endif
</div>