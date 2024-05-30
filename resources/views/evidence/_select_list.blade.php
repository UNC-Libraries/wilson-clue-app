@foreach($evidence as $e)
<div class="media evidence-media" data-id="{{ $e->id }}">
    <div class="media-left">
        {{ html()->img(asset($e->src))->class('media-object-64') }}
    </div>
    <div class="media-body">
        <h4 class="media-heading">{{ $e->title }}</h4>
    </div>
</div>
@endforeach
