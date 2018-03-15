<div class="question-div {{ $quest->suspect->machine }} {{ $quest->completedBy->pluck('id')->contains($team->id) ? 'correct' : '' }}">
    <div class="row">
        @if($quest->completedBy->pluck('id')->contains($team->id))
            <div class="col-xs-12 text-center"><strong>Complete</strong></div>
        @else
            <div class="col-xs-12 text-center"><div id="minigameForm-response" class="question-response"></div></div>
            <p class="text-center">Find the images and arrange them in chronological order.</p>
            <p class="text-center">Click on an image to enlarge it.</p>
            {!! Form::open(['route' => ['ui.attempt.minigame', $quest->id], 'id' => 'minigameForm', 'class' => 'text-center']) !!}
                {!! Form::hidden('attempt', null, ['class' => 'form-control']) !!}
                <input type="submit" class="btn btn-large btn-{{ $quest->suspect->bootstrap_color }}" value="Submit">
            {!! Form::close() !!}
        @endif
    </div>
</div>

@if(!$quest->completedBy->pluck('id')->contains($team->id))
    <div class="row" id="minigameContainer">
        @foreach($quest->minigameImages->shuffle() as $img)
            <div class="col-xs-4 minigame-image draggable" style="background-image: url('{{ asset($img->src) }}')" data-id="{{ $img->id }}">
                <span class="order"></span>
            </div>
        @endforeach
    </div>

    @foreach($quest->minigameImages as $img)
        <div class="modal fade" id="minigameImageModal-{{ $img->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <img class="img-responsive" src="{{ asset($img->src) }}">
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif