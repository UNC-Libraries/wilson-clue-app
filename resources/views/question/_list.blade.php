@foreach($models as $question)
    <div class="media question-media" data-id="{{ $question->id }}">
        <div class="media-left">
            @if($question->type == 1)
                {!! Html::image(asset($question->src),'question image',array('class'=>'media-object-64')) !!}
            @else
                {!! Html::image(asset('/images/txt.jpg'),'',array('class'=>'media-object-64')) !!}
            @endif
        </div>
        <div class="media-body">
            <h4 class="media-heading">{{ $question->text }}</h4>
            {{ $question->full_answer }}
        </div>
    </div>
@endforeach
