<div class="row">
    @foreach($evidence as $e)
        <div class="col-6 col-xs-4 col-sm-3">
            <div class="card card-body text-center">
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