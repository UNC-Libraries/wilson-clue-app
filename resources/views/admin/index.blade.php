@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="jumbotron h-100 p-5 bg-light border rounded-3">
        <div class="container">
            <h1>Wilson Clue! App Administration</h1>
            <p>Edit/Add assets, manage games, and update site messages.</p>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>Games</h2>
                <a href="{{ route('admin.game.create') }}" class="float-end btn btn-success"><span class="fa fa-plus"></span> Add New</a>
                <table class="table">
                    <thead>
                        <tr><th>Name</th><th>Status</th><th>Activate</th></tr>
                    </thead>
                    <tbody>
                        @foreach($games->sortBy('start_time') as $game)
                        <tr>
                            <td><a href="{{ route('admin.game.show', $game->id) }}">{{$game->name}}</a></td>
                            <td>{{ $game->statusText }}</td>
                            <td>
                                @if(!$game->active)
                                    <a href="{{ route('admin.game.activate', $game->id) }}" class="btn btm-primary btn-sm">Activate</a>
                                @else
                                    <a href="{{ route('admin.game.deactivate', $game->id) }}" class="btn btn-danger btn-sm">Deactivate</a>
                                @endif

                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-end">
                                <a href="{{ route('admin.trash') }}" class="text-danger">View Deleted games</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-12">
                <h2>Site Assets</h2>
                <div class="row">
                    @foreach($assets as $asset)
                        <div class="col-12 col-sm-6 col-md-4">
                            <a href="{{ route($asset['route']) }}">
                                <div class="card card-body well-sm text-center">
                                    <span class="fa fa-5x fa-{{ $asset['icon'] }}"></span>
                                    <span style="display:block; font-size: 1.2em;">{{ $asset['name'] }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@stop