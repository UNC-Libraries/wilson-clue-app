<!-- title -->
<div class="form-group">
    {{ html()->label('Name', 'name') }}
    {{ html()->text('name')->class('form-control') }}
</div>

<!-- date -->
<div class="form-group">
    {{ html()->label('Start Time', 'start_time') }}
    {{ html()->text('start_time', '')->class('form-control date datetime-picker')->data('date-default-date', $game->start_time ? $game->start_time->toIso8601String() : \Carbon\Carbon::now()->addDays(1)->toIso8601String()) }}
    {{ html()->label('End Time', 'end_time') }}
    {{ html()->text('end_time', '')->class('form-control date datetime-picker')->data('date-default-date', $game->end_time ? $game->end_time->toIso8601String() : \Carbon\Carbon::now()->addDays(1)->addHours(1)->addMinutes(30)->toIso8601String()) }}
</div>

<!-- max teams -->
<div class="form-group">
    {{ html()->label('Max Teams', 'max_teams') }}
    {{ html()->number('max_teams')->class('form-control') }}
</div>

<fieldset class="form-group">
    <legend>Restrictions</legend>
    <label class="radio-inline">
        <input type="radio" name="students_only" value="1" {{ $game->students_only === null || $game->students_only ? 'checked' : '' }}> Students Only
    </label>
    <label class="radio-inline">
        <input type="radio" name="students_only" value="0" {{ $game->students_only === false ? 'checked' : '' }}> Any UNC Affiliate
    </label>
</fieldset>