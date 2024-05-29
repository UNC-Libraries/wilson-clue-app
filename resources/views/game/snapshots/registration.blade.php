<!-- Registration Snapshop -->
<div class="col-xs-12">
    <div class="dash-section">
        <div class="dash-section-header">
            <h3>
                Teams
                @if($game->registration != 1)
                    {{ html()->form('PUT', route('admin.game.update', [$game->id]))->style('display: inline-block;')->open() }}
                    <input type="hidden" name="registration" value="1">
                    <button type="submit" class="btn btn-success btn-sm">Open Registration</button>
                    {{ html()->form()->close() }}
                @else
                    {{ html()->form('PUT', route('admin.game.update', [$game->id]))->style('display: inline-block;')->open() }}
                    <input type="hidden" name="registration" value="0">
                    <button type="submit" class="btn btn-danger btn-sm">Close Registration</button>
                    {{ html()->form()->close() }}
                @endif
            </h3>
        </div>
        <div class="dash-section-body">
            <h4>
                Registration
                <a href="{{ route('admin.game.teams', $game->id) }}" type="button" class="btn btn-primary btn-xs">
                    <span class="fa fa-users"></span> Manage
                </a>
            </h4>
            <div class="progress">
                <div class="progress-bar progress-bar-info" style="width: {{ ($game->registeredTeams->count() / ($game->max_teams + $game->waitlistTeams->count()) ) * 100  }}%">
                    <span>{{ $game->registeredTeams->count() }} of {{ $game->max_teams }}</span>
                </div>
                <div class="progress-bar progress-bar-warning" style="width: {{ ($game->waitlistTeams->count() / ($game->max_teams + $game->waitlistTeams->count()) ) * 100  }}%">
                    <span>{{ $game->waitlistTeams->count() }} waitlist</span>
                </div>
            </div>

            <h4>
                Player Breakdown
                <a href="{{ route('admin.player.index', $game->id) }}?sort_by=last_name&sort_order=asc&game[]={{ $game->id }}" type="button" class="btn btn-primary btn-xs">
                    <span class="fa fa-vcard-o"></span> View All
                </a>
            </h4>
            <p><small><em>These numbers include all players who have registered, including waitlist teams</em></small></p>
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <h5>Student Type</h5>
                    <table class="table">
                        <thead>
                        <tr><th>Type</th><th>Count</th></tr>
                        </thead>
                        <tbody>
                        @foreach($players->groupBy('class') as $class => $array)
                            <tr><td>{{ $class }}</td><td>{{ $array->count() }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <h5>Academic Group</h5>
                    <table class="table">
                        <thead>
                        <tr><th>Group</th><th>Count</th></tr>
                        </thead>
                        <tbody>
                        @foreach($players->groupBy('academic_group') as $group => $array)
                            <tr><td>{{ $group }}</td><td>{{ $array->count() }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>