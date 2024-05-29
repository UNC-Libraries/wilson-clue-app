<table class="table">
    <thead>
    <tr>
        <th></th>
        <th>Name</th>
        <th>Dietary Restrictions</th>
        <th>Warnings</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @foreach($teams as $team)
        <tr>
            <td><a href="{{ route('admin.team.edit',array($team->id)) }}" class="btn btn-default">Edit</a></td>
            <td>{{ $team->name }}</td>
            <td>{{ $team->dietary }}</td>
            <td>
                @if(count($team->players) < $team::MINIMUM_PLAYERS)
                    <div class="text-warning"><span class="fa fa-warning"></span> Not Enough Players</div>
                @endif
            </td>
            <td>
                {{ html()->modelForm($team, 'POST', route('admin.team.waitlist', [$team->id]))->open() }}
                    @if($team->waitlist)
                        <button type="submit" class="btn btn-danger"><span class="fa fa-caret-up"></span></button>
                    @else
                        <button type="submit" class="btn btn-primary"><span class="fa fa-caret-down"></span></button>
                    @endif
                {{ html()->closeModelForm() }}
            </td>
    @endforeach
    </tbody>
</table>