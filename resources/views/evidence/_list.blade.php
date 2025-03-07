<div class="row">
    @foreach($evidence as $e)
        <div class="col-6 col-sm-4 col-md-3">
            <div class="card card-body text-center">
                <div class="text-center">
                    {{ html()->img(asset($e->src))->class('media-object-128') }}
                </div>
                <p style="min-height: 40px;">{{ $e->title }}</p>
                <a href="{{ route('admin.evidence.edit', $e->id) }}" class="btn btn-primary btn-sm">
                    <span class="fa fa-edit"></span> Edit
                </a>
            </div>
        </div>
    @endforeach
</div>