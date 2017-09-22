<div class="row">
    @foreach($evidence as $e)
        <div class="col-xs-6 col-sm-4 col-md-3">
            <div class="well text-center">
                <div class="text-center">
                    {!! Html::image($e->src,null,array('class'=>'media-object-128')) !!}
                </div>
                <p style="min-height: 40px;">{{ $e->title }}</p>
                <a href="{{ route('admin.evidence.edit', $e->id) }}" class="btn btn-primary btn-sm">
                    <span class="fa fa-edit"></span> Edit
                </a>
            </div>
        </div>
    @endforeach
</div>