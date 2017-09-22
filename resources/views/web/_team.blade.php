<div class="team-card text-center {{$class}}">
    <div class="team-name">{{ $team->name }}</div>
    @if($class == 'first-place')
        {!! Html::image(asset('images/star_full.png'), 'First Place', array('class'=>'clip-circle')) !!}
    @elseif($class == 'second-place')
        {!! Html::image(asset('images/star_empty.png'), 'Second Place', array('class'=>'clip-circle')) !!}
    @elseif($class == 'third-place')
        {!! Html::image(asset('images/star_bronze.png'), 'Third Place', array('class'=>'clip-circle')) !!}
    @endif
    @if($team->score > 0)
        <p>Score: {{$team->score}}</p>
    @endif
    <ul class="list-unstyled list-inline">
        @foreach($team->players as $player)
            <li>{{$player->full_name}}</li>
        @endforeach
    </ul>
</div>