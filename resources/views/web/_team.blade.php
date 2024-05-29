<div class="team-card text-center {{$class}}">
    <div class="team-name">{{ $team->name }}</div>
    @if($class == 'first-place')
        {{ html()->img(asset(asset('images/star_full.png')), 'First Place')->class('clip-circle') }}
    @elseif($class == 'second-place')
        {{ html()->img(asset(asset('images/star_empty.png')), 'Second Place')->class('clip-circle') }}
    @elseif($class == 'third-place')
        {{ html()->img(asset(asset('images/star_bronze.png')), 'Third Place')->class('clip-circle') }}
    @endif
    @if($team->score > 0)
        <p>Score: {{$team->score}}</p>
    @endif
</div>