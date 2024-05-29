<div class="question-div">
    <div class="col-xs-12 text-center"><div id="dnaForm-response" class="question-response"></div></div>
    {{ html()->form('POST', route('ui.attempt.dna', ))->id('dnaForm')->open() }}
        <div class="form-group">
            {{ html()->label('Sequence', 'attempt') }}
            {{ html()->text('attempt')->class('form-control') }}
        </div>
        <input type="submit" class="btn btn-default" value="Submit">
    {{ html()->form()->close() }}
</div>