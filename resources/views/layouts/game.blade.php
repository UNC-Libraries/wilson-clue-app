@extends('layouts.admin', ['title' => 'Clue - Admin'])


@section('content')

    <div class="container-fluid" id="gameContainer">
        <div class="row">
            <div class="col-1" id="subnav">
                @include('partials.admin.subnav',
                    ['nav_items' => [
                        [
                            'text' => 'Dashboard',
                            'route' => route('admin.game.show', $game->id),
                            'active' => Route::currentRouteName() ==  'admin.game.show',
                            'icon' => 'tachometer'
                        ],
                        [
                            'text' => 'Settings',
                            'route' => route('admin.game.edit',$game->id),
                            'active' => in_array(Route::currentRouteName(),
                                            ['admin.game.edit','admin.game.quest.edit', 'admin.game.edit.evidence']),
                            'icon' => 'cogs'
                        ],
                        [
                            'text' => 'Teams',
                            'route' => route('admin.game.teams',$game->id),
                            'active' => in_array(Route::currentRouteName(), ['admin.game.teams', 'admin.team.edit']),
                            'icon' => 'users'
                        ],
                        [
                            'text' => 'Player Check-in',
                            'route' => route('admin.game.checkin',$game->id),
                            'active' => in_array(Route::currentRouteName(), ['admin.game.checkin']),
                            'icon' => 'id-card-o'
                        ],
                        [
                            'text' => 'Scoring',
                            'route' => route('admin.game.score',$game->id),
                            'active' => in_array(Route::currentRouteName(), ['admin.game.score', 'admin.game.judge']),
                            'icon' => 'check-square'
                        ],
                        [
                            'text' => 'Archive',
                            'route' => route('admin.game.edit.archive',$game->id),
                            'active' => Route::currentRouteName() ==  'admin.game.edit.archive',
                            'icon' => 'file-archive-o'
                        ],
                    ]]
                )
            </div>
            <div class="col-11"  id="contentWrapper">
                @yield('breadcrumb')
                <h1 class="text-center">{{ $game->name }} <small>{{ $game->statusText }}</small></h1>
                @yield('game.content')
            </div>
        </div>
    </div>

@endsection