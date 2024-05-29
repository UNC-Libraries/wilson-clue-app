{{ html()->form('POST', route('ui.attempt.question', [$q->id]))->class('question-form')->id('question-' . $q->id)->open() }}
    <div class="form-group">
        {{ html()->label('Answer', 'attempt-for-' . $q->id) }}
        {{ html()->text('attempt')->class('form-control')->id('attempt-for-' . $q->id) }}
    </div>
    <input type="submit" class="btn btn-{{ $quest->suspect->bootstrap_color }} pull-right" value="Submit">
{{ html()->form()->close() }}