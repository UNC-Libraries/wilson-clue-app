<div class="row">
    <!-- Question type -->
    <div class="form-group col-xs-12 col-sm-4">
        <div class="checkbox" id="questionType">
            <label>
                {{ html()->checkbox('type', false) }} Image?
            </label>
        </div>
    </div>
    <!-- Question Location -->
    <div class="form-group col-xs-12 col-sm-4">
        {{ html()->label('Location', 'location_id') }}
        {{ html()->select('location_id', $locations->pluck('name', 'id'), $question->location ? $question->location->id : null)->placeholder('Select a location')->class('form-control')->required() }}
    </div>
</div>
<div class="row">
    <!-- Question text -->
    <div class="form-group col-xs-12">
        {{ html()->label('Question Text', 'text') }}
        {{ html()->text('text')->class('form-control') }}
    </div>
</div>
<div class="row hidden" id="questionImageRow">
    <div class="col-xs-12">
        <!-- Question Image -->
        @include('partials._image_input',['current' => $question->src, 'alt' => $question->id])
    </div>
</div>
<div class="row" style="margin-top: 1em;">
    <div class="col-xs-12">
        <legend>Answers</legend>
        @if(!empty($incorrect))
            <div class="well">
                <p class="lead">Incorrect Attempted Answers</p>
                <ul class="list-unstyled list-inline">
                    @foreach($incorrect->sortByDesc('count') as $i)
                        <li style="border: 1px solid black; padding: 5px;">
                            {{ $i['answer'] }} <span class="badge">{{ $i['count'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row">
            <div class="form-group col-xs-12">
                {{ html()->label('Full Answer', 'full_answer') }}
                {{ html()->text('full_answer')->class('form-control') }}
                <span class="help-block">
                    The full answer is context for the judges. You must enter single-word answers below. Usually keywords
                    from the full answer.
                </span>
            </div>
        </div>
        <div class="row">
            @foreach($question->answers as $answer)
                @include('question._answer_input')
            @endforeach
            <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2 col-top-padding">
                <button type="button" class="btn btn-success" id="addNewAnswer" data-url="{{ route('admin.new.answer') }}">
                    <span class="fa fa-plus-circle"></span>
                </button>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="form-group col-xs-12 text-right">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>