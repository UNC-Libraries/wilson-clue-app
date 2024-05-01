<!-- title -->
<div class="form-group">
    {!! Form::label('name','Name') !!}
    {!! Form::text('name',null,array('class' => 'form-control')) !!}
</div>

<!-- date -->
<div class="form-group">
    {!! Form::label('start_time','Start Time') !!}
    {!! Form::text(
        'start_time',
        '',
        ['class' => 'form-control date datetime-picker', 'data-date-default-date' => $game->start_time ? $game->start_time->toIso8601String() : \Carbon\Carbon::now()->addDays(1)->toIso8601String()]
    ) !!}
    {!! Form::label('end_time','End Time') !!}
    {!! Form::text(
        'end_time',
        '',
        ['class' => 'form-control date datetime-picker', 'data-date-default-date' => $game->end_time ? $game->end_time->toIso8601String() : \Carbon\Carbon::now()->addDays(1)->addHours(1)->addMinutes(30)->toIso8601String(),]
    ) !!}
</div>

<!-- max teams -->
<div class="form-group">
    {!! Form::label('max_teams','Max Teams') !!}
    {!! Form::number('max_teams',null,array('class' => 'form-control')) !!}
</div>

<fieldset class="form-group">
    <legend>Restrictions</legend>
    <label class="form-check-inline">
        <input type="radio" name="students_only" value="1" {{ $game->students_only === null || $game->students_only ? 'checked' : '' }}> Students Only
    </label>
    <label class="form-check-inline">
        <input type="radio" name="students_only" value="0" {{ $game->students_only === false ? 'checked' : '' }}> Any UNC Affiliate
    </label>
</fieldset>