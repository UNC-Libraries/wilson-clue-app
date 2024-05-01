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
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Games <span class="caret"></span></a>
                    <div class="dropdown-menu">
                        @foreach($games->sortBy('start_time') as $game)
                            <a class="dropdown-item" href="{{ route('admin.game.show', $game->id) }}">{{ $game->name }}</a>
                        @endforeach
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Assets <span class="caret"></span></a>
                    <div class="dropdown-menu">
                        @foreach($assets as $asset)
                            <a class="dropdown-item" href="{{ route($asset['route']) }}">{{ $asset['name'] }}</a>
                        @endforeach
                    </div>
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