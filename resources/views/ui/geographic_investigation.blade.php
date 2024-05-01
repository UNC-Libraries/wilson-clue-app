@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    @include('partials._maps')
    <div class="container-fluid">
        @include('ui._quest_header', [
            'img_src' => asset('images/compass.png'),
            'quest_title' => 'Geographic Investigation',
            'quest_subtitle' => $game->geographicInvestigationLocation->name,
            'color' => 'default',
            'map_section' => $game->geographicInvestigationLocation->map_section,
            'map_color' => 'magenta',
            'text' => trans('ui.geographicInvestigation')
        ])
        <div class="row">
            <div class="col-12">
                <p class="lead">Everything you need to track down the portal is in the Center for Geographic Investigations in the Fearrington Reading Room.</p>
            </div>
        </div>
    </div>
@endsection