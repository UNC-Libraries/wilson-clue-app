<div class="row">
    <!-- Question type -->
    <div class="form-group col-12 col-sm-3 mb-3">
        <div id="questionType">
            <label>
                {!! Form::checkbox('type') !!} Image?
            </label>
        </div>
    </div>
    <!-- Question Location -->
    <div class="form-group col-12 col-sm-3">
        {!! Form::label('location_id','Location', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::select('location_id',$locations->pluck('name','id'), $question->location ? $question->location->id : null, array('placeholder' => 'Select a location', 'class' => 'form-control', 'required')) !!}
    </div>
</div>
<div class="row">
    <!-- Question text -->
    <div class="form-group col-12 col-sm-3">
        {!! Form::label('text', 'Question Text', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('text', null, ['class'=>'form-control']) !!}
    </div>
</div>
<div class="row d-none" id="questionImageRow">
    <div class="col">
        <!-- Question Image -->
        @include('partials._image_input',['current' => $question->src, 'alt' => $question->id])
    </div>
</div>
<div class="row" style="margin-top: 1em;">
    <div class="col-12">
        <legend>Answers</legend>
        @if(!empty($incorrect))
            <div class="card card-body">
                <p class="lead">Incorrect Attempted Answers</p>
                <ul class="list-unstyled list-inline">
                    @foreach($incorrect->sortByDesc('count') as $i)
                        <li style="border: 1px solid black; padding: 5px;">
                            {{ $i['answer'] }} <span class="badge badge-pill">{{ $i['count'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row">
            <div class="form-group col-12">
                {!! Form::label('full_answer', 'Full Answer', ['class' => 'fw-bold mb-1']) !!}
                {!! Form::text('full_answer',null,array('class' => 'form-control')) !!}
                <span class="form-text">
                    The full answer is context for the judges. You must enter single-word answers below. Usually keywords
                    from the full answer.
                </span>
            </div>
        </div>
        <div class="row">
            @foreach($question->answers as $answer)
                @include('question._answer_input')
            @endforeach
            <div class="col-12 col-xs-4 col-sm-3 col-md-2 col-top-padding">
                <button type="button" class="btn btn-success" id="addNewAnswer" data-url="{{ route('admin.new.answer') }}">
                    <span class="fa fa-plus-circle"></span>
                </button>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="form-group col-12 text-end">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>