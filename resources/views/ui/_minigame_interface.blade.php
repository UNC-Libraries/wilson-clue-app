<div class="question-div {{ $quest->suspect->machine }} {{ $quest->completedBy->pluck('id')->contains($team->id) ? 'correct' : '' }}">
    <div class="row">
        @if($quest->completedBy->pluck('id')->contains($team->id))
            <div class="col-12 text-center"><strong>Complete</strong></div>
        @else
            <div class="col-12 text-center"><div id="minigameForm-response" class="question-response"></div></div>
            <p class="text-center">Find the images and arrange them in chronological order.</p>
            <p class="text-center">Click on an image to enlarge it.</p>
            {{ html()->form('POST', route('ui.attempt.minigame', [$quest->id]))->id('minigameForm')->class('text-center')->open() }}
                {{ html()->hidden('attempt')->class('form-control') }}
                <input type="submit" class="btn btn-large btn-{{ $quest->suspect->bootstrap_color }}" value="Submit">
            {{ html()->form()->close() }}
        @endif
    </div>
</div>

@if(!$quest->completedBy->pluck('id')->contains($team->id))
    <div class="row" id="minigameContainer">
        @foreach($quest->minigameImages->shuffle() as $img)
            <div class="col-4 minigame-image draggable" style="background-image: url('{{ asset($img->src) }}')" data-id="{{ $img->id }}">
                <span class="order"></span>
            </div>
        @endforeach
    </div>

    @foreach($quest->minigameImages as $img)
        <div class="modal fade" id="minigameImageModal-{{ $img->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <img alt="" class="img-fluid" src="{{ asset($img->src) }}">
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif