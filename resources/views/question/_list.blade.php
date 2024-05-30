@foreach($models as $question)
    <div class="media question-media" data-id="{{ $question->id }}">
        <div class="media-left">
            @if($question->type == 1)
                {{ html()->img(asset(asset($question->src)), 'question image')->class('media-object-64') }}
            @else
                {{ html()->img(asset(asset('/images/txt.jpg')), '')->class('media-object-64') }}
            @endif
        </div>
        <div class="media-body">
            <h4 class="media-heading">{{ $question->text }}</h4>
            {{ $question->full_answer }}
        </div>
    </div>
@endforeach
