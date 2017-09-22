{!! Form::open(['route' => ['ui.attempt.question',$q->id], 'class' => 'question-form', 'id' => 'question-'.$q->id]) !!}
    <div class="form-group">
        {!! Form::label('attempt-for-'.$q->id, 'Answer') !!}
        {!! Form::text('attempt', null, ['class' => 'form-control', 'id' => 'attempt-for-'.$q->id]) !!}
    </div>
    <input type="submit" class="btn btn-{{ $quest->suspect->bootstrap_color }} pull-right" value="Submit">
{!! Form::close() !!}