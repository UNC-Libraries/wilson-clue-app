@extends('layouts.web', ['title' => 'Clue! Presented By Wilson Library'])

@section('content')

    <section class="main-section">
        <div class="container">
            <div class="row">
                <div class="col-12 subpage-banner">
                    <h1><a href="{{ route('web.index') }}">Clue</a> <small class="text-right">{{$game->start_time->format('F, jS Y')}}</small></h1>
                </div>
                <div class="col-12 col-sm-10 offset-md-1 col-md-8 offset-lg-2">
                    @if($game->flickr)
                        <h2 class="text-center">Security Footage</h2>
                        <a data-flickr-embed="true"  href="https://www.flickr.com/photos/unclibraries/albums/{{ $game->flickr }}" title="Wilson Library presents Clue, {{ $game->name }}">
                            <img width="100%" height="auto" src="{{ $game->flickr_start_img }}" alt="Wilson Library presents Clue, {{  $game->name }}">
                        </a>
                        <script async src="http://embedr.flickr.com/assets/client-code.js" charset="utf-8"></script>
                        <p class="text-center"><a href="https://www.flickr.com/photos/unclibraries/sets/{{ $game->flickr }}" target="_blank">View Flickr Album</a></p>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <section class="light-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center">The Teams</h2>
                    <div class="row">

                            <div class="col-12">
                                @if($first_place)
                                    @include('web._team',['team' => $first_place, 'class' => 'first-place'])
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-xs-6">
                                @if($second_place)
                                    @include('web._team',['team' => $second_place, 'class' => 'second-place'])
                                @endif
                            </div>
                            <div class="col col-xs-6">
                                @if($third_place)
                                    @include('web._team',['team' => $third_place, 'class' => 'third-place'])
                                @endif
                            </div>
                        </div>
                        @foreach($teams->slice(3)->chunk(3) as $chunk)
                            <div class="row">
                                @foreach($chunk as $team)
                                    <div class="col col-xs-4">
                                        @include('web._team',['team' => $team, 'class' => ''])
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <div class="col-12">
                        {!! app(Parsedown::class)->text($game->team_accolades) !!}
                    </div>
                </div>

        </div>
    </section>

    <section class="mid-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    {!! app(Parsedown::class)->text($game->special_thanks) !!}
                </div>
            </div>
        </div>
    </section>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Event",
        "endDate": "{{$game->start_time->format('Y-m-d')}}",
        "startDate": "{{$game->start_time->format('Y-m-d')}}>",
        "name": "Wilson Clue - {{$game->name}}",
        "location": {
            "@type":"Place",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Chapel Hill",
                "addressRegion": "NC",
                "postalCode": "27514",
                "streetAddress": "Wilson Library"
            },
            "name": "Wilson Library"
        },
        "sameAs": "http://clue.unc.edu",
        "organizer":
        {
            "@id":"http://library.unc.edu/wilson/"
        },
        "url": "http://clue.unc.edu/archive/{{$game->id}}"
    }
</script>

@stop