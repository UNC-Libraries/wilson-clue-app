<div class="col-12 col-sm-4 col-md-3 col-lg-2 answer-wrapper col-top-padding">
    <div class="input-group">
        <span class="input-group-btn">
            <button class="btn btn-danger remove-answer" data-url="{{ $answer->id ? route('admin.destroy.answer', $answer->id) : '' }}" type="button">
                <span class="fa fa-trash"></span>
            </button>
        </span>

        @if($answer->id)
            {{ html()->text('answer[' . $answer->id . ']', $answer->text)->class('form-control') }}
        @else
            {{ html()->text('answer[new][]', $answer->text)->class('form-control') }}
        @endif
    </div>
</div>