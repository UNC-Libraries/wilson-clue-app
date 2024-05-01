<div class="question-div">
    <div class="col-12 text-center"><div id="dnaForm-response" class="question-response"></div></div>
    {!! Form::open(['route' => ['ui.attempt.dna'], 'id' => 'dnaForm']) !!}
        <div class="form-group">
            {!! Form::label('attempt', 'Sequence') !!}
            {!! Form::text('attempt', null, ['class' => 'form-control']) !!}
        </div>
        <input type="submit" class="btn btn-secondary" value="Submit">
    {!! Form::close() !!}
</div>