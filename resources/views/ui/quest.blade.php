@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    @include('partials._maps')
    <div class="container-fluid">
        @include('ui._quest_header', [
            'img_src' => asset($quest->suspect->face_image),
            'quest_title' => $quest->suspect->name,
            'quest_subtitle' => $quest->location->name,
            'color' => $quest->suspect->bootstrap_color,
            'map_section' => $quest->location->mapSection->name,
            'map_color' => $quest->suspect->bootstrap_color,
            'quest_id' => $quest->id
        ])
        <div class="row">
            <div class="col-xs-12">
                @include('ui._'.$quest->type.'_interface')
            </div>
        </div>
    </div>
@endsection