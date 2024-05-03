@extends('layouts.master', ['title' => 'Clue - Admin'])

@section('css')
    <!-- css -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet" type="text/css" >
@endsection


@section('main.content')
    <nav class="navbar navbar-expand-md navbar-light bg-light px-4" id="homepage-nav">
        <a class="navbar-brand" href="{{ route('admin') }}">Clue Administration</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#the-nav" aria-controls="the-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="the-nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.game.create') }}"><span class="fa fa-plus"></span> New Game</a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="gamesDropdown" data-bs-toggle="dropdown" role="button" aria-expanded="false">Games</a>
                    <ul class="dropdown-menu" aria-labelledby="gamesDropdown">
                        @foreach($games->sortBy('start_time') as $game)
                            <li><a class="dropdown-item" href="{{ route('admin.game.show', $game->id) }}">{{ $game->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="assetsDropdown" data-bs-toggle="dropdown" role="button" aria-expanded="false">Assets</a>
                    <ul class="dropdown-menu" aria-labelledby="gamesDropdown">
                        @foreach($assets as $asset)
                            <li><a class="dropdown-item" href="{{ route($asset['route']) }}">{{ $asset['name'] }}</a></li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.logout') }}">Logout</a></li>
            </ul>
        </div>
    </nav>

    @yield('content')
    @yield('modal')
    @yield('pageGuide')

@endsection