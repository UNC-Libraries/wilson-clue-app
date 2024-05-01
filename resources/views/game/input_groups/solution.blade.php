<div class="form-group col-12 col-xs-4">
    <label for="solutionSuspect">Ghost</label>
    <select name="suspect_id" id="solutionSuspect" class="form-control">
        @foreach($game->quests->pluck('suspect') as $suspect)
            <option value="{{ $suspect->id }}" {{ $game->suspect_id == $suspect->id ? "selected" : "" }}>{{ $suspect->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group col-12 col-xs-4">
    <label for="solutionPortal">Portal</label>
    <select name="location_id" id="solutionPortal" class="form-control">
        @foreach($game->quests->pluck('location') as $location)
            <option value="{{ $location->id }}" {{ $game->location_id == $location->id ? "selected" : "" }}>{{ $location->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group col-12 col-xs-4">
    <label for="solutionEvidence">Evidence</label>
    <select name="evidence_id" id="solutionEvidence" class="form-control">
        <option value="" disabled selected>Select Evidence</option>
        @foreach($game->evidence as $e)
            <option value="{{ $e->id }}" {{ $game->evidence_id == $e->id ? "selected" : "" }}>{{ $e->title }}</option>
        @endforeach
    </select>
</div>