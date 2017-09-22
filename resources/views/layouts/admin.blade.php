@extends('layouts.master', ['title' => 'Clue - Admin'])

@section('css')
    <!-- css -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet" type="text/css" >
@endsection


@section('main.content')
    <nav class="navbar navbar-default" id="homepage-nav">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#the-nav" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{ route('admin') }}">Clue Administration</a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar">
                    <li><a href="{{ route('admin.game.create') }}"><span class="fa fa-plus"></span> New Game</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Games <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            @foreach($games->sortBy('start_time') as $game)
                                <li><a href="{{ route('admin.game.show', $game->id) }}">{{ $game->name }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Assets <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            @foreach($assets as $asset)
                                <li><a href="{{ route($asset['route']) }}">{{ $asset['name'] }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="{{ route('admin.logout') }}">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')
    @yield('modal')
    @yield('pageGuide')

@endsection