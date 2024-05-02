@extends('layouts.master', ['title' => 'Clue - Admin'])

@section('css')
    <!-- css -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet" type="text/css" >
@endsection


@section('main.content')
    <nav class="navbar navbar-expand-lg navbar-light bg-light" id="homepage-nav">
        <a class="navbar-brand" href="{{ route('admin') }}">Clue Administration</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#the-nav" aria-controls="the-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="the-nav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.game.create') }}"><span class="fa fa-plus"></span> New Game</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Games <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        @foreach($games->sortBy('start_time') as $game)
                            <li><a href="{{ route('admin.game.show', $game->id) }}">{{ $game->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Assets <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        @foreach($assets as $asset)
                            <li class="dropdown-item"><a href="{{ route($asset['route']) }}">{{ $asset['name'] }}</a></li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.logout') }}">Logout</a></li>
            </ul>
        </div>
    </nav>

    @yield('content')
    @yield('modal')
    @yield('pageGuide')

@endsection