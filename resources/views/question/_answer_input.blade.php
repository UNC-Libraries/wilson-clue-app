<div class="col-12 col-xs-4 col-sm-3 col-md-2 answer-wrapper col-top-padding">
    <div class="input-group">
        <span class="input-group-btn">
            <button class="btn btn-danger remove-answer" data-url="{{ $answer->id ? route('admin.destroy.answer', $answer->id) : '' }}" type="button">
                <span class="fa fa-trash"></span>
            </button>
        </span>

        @if($answer->id)
            {!! Form::text('answer['.$answer->id.']', $answer->text, array('class' => 'form-control')) !!}
        @else
            {!! Form::text('answer[new][]', $answer->text, array('class' => 'form-control')) !!}
        @endif
    </div>
</div>