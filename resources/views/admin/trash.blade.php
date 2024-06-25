@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        {!! Breadcrumbs::render('admin.trash') !!}
        <div class="row">
            <h1>Deleted Games</h1>
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Start Date</th>
                    <th>Created On</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($games as $game)
                    <tr>
                        <td>
                            {{ html()->form('POST', route('admin.restore', [$game->id]))->open() }}
                            <button type="submit" class="btn btn-primary"><span class="fa fa-history"></span> Restore</button>
                            {{ html()->form()->close() }}
                        </td>
                        <td>{{ $game->name }}</td>
                        <td>{{ $game->start_time->format('M d Y') }}</td>
                        <td>{{ $game->created_at->format('Y-m-d H:i:s A') }}</td>
                        <td>
                            @include('partials._delete_form', [
                                'route' => ['admin.delete', $game->id],
                                'message' => 'This will permanently delete this game and all associated teams. \n Please confirm'
                            ])
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop