@extends('layouts.game', ['title' => 'Clue - '.$game->name])

@section('breadcrumb')
    {!! Breadcrumbs::render('admin.game.judgement',$game) !!}
@stop

@section('game.content')

    @include('admin._alert')

    <div class="row">
        <div class="col-12">
            <p class="lead text-center">Judge incorrect answers</p>
        </div>
        @foreach($game->quests as $quest)
            <!-- {{ $quest->id }} -->
            <div class="col-12">
                <div class="dash-section">
                    <div class="dash-section-header">
                        <h3>{{ $quest->suspect->name }} -- {{ $quest->location->name }}</h3>
                    </div>
                    <div class="dash-section-body">
                        @if($quest->needs_judgement)
                            @include('game._judge_quest', ['quest' => $quest, 'game' => $game])
                        @else
                            <h2>Nothing to Judge!</h2>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop