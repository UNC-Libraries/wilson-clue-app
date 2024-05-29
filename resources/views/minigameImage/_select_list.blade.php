@foreach($images as $image)
<div class="media evidence-media" data-id="{{ $image->id }}">
    <div class="media-left">
        {{ html()->img(asset($image->src))->class('media-object-64') }}
    </div>
    <div class="media-body">
        <h4 class="media-heading">{{ $image->name }}</h4>
    </div>
</div>
@endforeach
