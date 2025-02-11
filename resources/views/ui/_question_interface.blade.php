@foreach($quest->questions as $q)
    <div class="question-div {{ $quest->suspect->machine }} {{ $q->completedBy()->pluck('id')->contains($team->id) ? 'correct' : '' }}">
        <div class="row">
            @if($q->completedBy()->pluck('id')->contains($team->id))
                <div class="col-12 text-center"><strong>Complete</strong></div>
            @else
                <div class="col-12 text-center"><div id="question-{{ $q->id }}-response" class="question-response"></div></div>
                @if($q->type == 1)
                    <div class="col-6 col-sm-3">
                        <img src="{{ asset($q->src) }}" class="img-fluid">
                    </div>
                    <div class="col-6 col-sm-9">
                        <p class="lead">{{ $q->text }}</p>
                        @include('ui._question_form')
                    </div>
                @else
                    <div class="col-12">
                        <p class="lead">{{ $q->text }}</h3>
                        @include('ui._question_form')
                    </div>
                @endif
            @endif
        </div>
    </div>
@endforeach