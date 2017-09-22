@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    @include('partials._maps')
    <div class="container" style="margin-bottom: 2rem;">
        <div class="row">
            <div class="col-xs-12">
                <h1>Wilson Library Map</h1>
                @foreach($floors as $floor)
                    @include('ui._floor',['floor' => $floor])
                @endforeach
            </div>

        </div>
    </div>
@stop