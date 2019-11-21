<div class="col-xs-12 map-floor">
    <h2>{{ $floor->first()->floor_nth  }} Floor</h2>
    <div class="row">
        <div class="col-xs-7 col-md-4">
            <div class="col-xs-12 col-sm-4">
                <svg width="100%" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" version="1.1">
                    <use xlink:href="#baseMap"></use>
                    @foreach($floor as $location)
                        <use xlink:href="#{{ $location->map_section }}" class="map-{{ $location->quests->first()->suspect->bootstrap_color ?? 'base'}}"></use>
                    @endforeach
                </svg>
            </div>
        </div>
        <div class="col-xs-5 col-md-8 text-left">
            <div class="row">
                @foreach($floor as $location)
                    @include('ui._floor_button', ['route' => route('ui.quest', $location->quests->first()->id), 'image' => asset($location->quests->first()->suspect->face_image), 'name' => $location->name])
                @endforeach
                @if($floor->first()->floor == $game->evidenceLocation->floor)
                    @include('ui._floor_button', ['route' => route('ui.evidence'), 'image' => 'images/envelope_tiny.png', 'name' => $game->evidenceLocation->name])
                @endif
                @if($floor->first()->floor == $game->geographicInvestigationLocation->floor)
                    @include('ui._floor_button', ['route' => route('ui.geographicInvestigation'), 'image' => 'images/compass.png', 'name' => $game->geographicInvestigationLocation->name])
                @endif
            </div>
        </div>
    </div>
</div>
<hr>