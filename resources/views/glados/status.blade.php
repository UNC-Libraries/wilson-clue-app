<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-9">
        @foreach($teams as $team)
            <h5>{{ $team->name }}</h5>
        <div class="progress team-status">
            @foreach($team->game_status->sortBy('time') as $status)
                <div class="progress-bar progress-bar-{{ $status['color'] }}" style="width: {{  1 / $team->game_status->count() * 100 }}%">
                    {{ $status['name'] }}
                </div>
            @endforeach
        </div>
        @endforeach
    </div>
    <div class="col-xs-12 col-sm-4 col-md-3">
        @if($teams->count() > 0)
            @foreach($quests as $quest)
                <h5>{{ $quest->suspect->name }}</h5>
                <div class="progress">
                    <div
                            class="progress-bar progress-bar-{{ $quest->suspect->machine }}"
                            style="width: {{ $quest->completedBy->count() / $teams->count() * 100 }}%">
                        {{ $quest->completedBy->count() }} of {{ $teams->count() }}
                    </div>
                </div>
            @endforeach
            <h5>Evidence Room</h5>
            <div class="progress">
                <div
                        class="progress-bar progress-bar-evidence"
                        style="width: {{ $teams->reject(function ($value, $key) { return empty($value); })->count() / $teams->count() * 100 }}%">
                    {{ $teams->reject(function ($value, $key) { return empty($value); })->count() }} of {{ $teams->count() }}
                </div>
            </div>
            <h5>Indictments</h5>
            <div class="progress">
                <div
                        class="progress-bar progress-bar-indictment"
                        style="width: {{ $teams->where('indictment_made', true)->count() / $teams->count() * 100 }}%">
                    {{ $teams->where('indictment_made', true)->count() }} of {{ $teams->count() }}
                </div>
            </div>
        @endif
    </div>
</div>