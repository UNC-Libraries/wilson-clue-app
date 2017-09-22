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
            <td><a href="{{ route('admin.team.edit',array('id'=>$team->id)) }}" class="btn btn-default">Edit</a></td>
            <td>{{ $team->name }}</td>
            <td>{{ $team->dietary }}</td>
            <td>
                @if(count($team->players) < 3)
                    <div class="text-warning"><span class="fa fa-warning"></span> Not Enough Players</div>
                @endif
            </td>
            <td>
                {!! Form::model($team, array('route' => array('admin.team.waitlist',$team->id))) !!}
                    @if($team->waitlist)
                        <button type="submit" class="btn btn-danger"><span class="fa fa-caret-up"></span></button>
                    @else
                        <button type="submit" class="btn btn-primary"><span class="fa fa-caret-down"></span></button>
                    @endif
                {!! Form::close() !!}
            </td>
    @endforeach
    </tbody>
</table>