@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    <div class="container" style="margin-bottom: 2rem;">
        <div class="row">
            <div class="col-xs-12" id="indictment">
                @if(empty($game->inProgress))
                    <div class="alert alert-danger text-center" role="alert">
                        <h5>The game has ended, please report back to the lobby!</h5>
                    </div>
                @elseif($team->indictment_made)
                    @include('ui._indictment_complete')
                @endif
            </div>

            <div class="col-xs-12">
                <h3 class="expo">Suspects</h3>
                @foreach($game->quests as $q)
                    @include('ui._quest_button', [
                        'route' => route('ui.quest',$q->id),
                        'image' => asset($q->suspect->tiny_image),
                        'title' => $q->suspect->name,
                        'percentComplete' => $progress[$q->id]['percentComplete'],
                        'progressMessage' => $progress[$q->id]['progressMessage'],
                        'color' => $q->suspect->bootstrap_color
                    ])
                @endforeach

                <h3 class="expo">Evidence</h3>
                @include('ui._quest_button', [
                    'route' => route('ui.evidence'),
                    'image' => asset('images/envelope_tiny.png'),
                    'title' => 'Evidence Room',
                    'percentComplete' =>'0',
                    'progressMessage' => 'Determine the target',
                    'color' => 'default'
                ])
                <h3 class="expo">Ghost DNA</h3>
                @include('ui._quest_button',[
                    'route' => route('ui.dna'),
                    'image' => asset('images/dna.png'),
                    'title' => 'Ghost DNA',
                    'percentComplete' => $team->foundDna->count() ? ceil($team->foundDna->count() / $dnaCount * 100) : 5,
                    'progressMessage' => $team->foundDna->count().' of '.$dnaCount,
                    'color' => 'default'
                ])
                <h3 class="expo">Solve</h3>
                @include('ui._quest_button', [
                    'route' => route('ui.indictment'),
                    'image' => asset('images/zoom_flip.png'),
                    'title' => 'Indictment',
                    'percentComplete' => '0',
                    'progressMessage' => 'Solve the case',
                    'color' => 'default'
                ])

                <h3 class="expo">Help</h3>
                @include('ui._quest_button', [
                    'route' => route('ui.map'),
                    'image' => asset('images/compass.png'),
                    'title' => 'Wilson Map',
                    'percentComplete' => '0',
                    'progressMessage' => 'Find your way',
                    'color' => 'default'
                ])

                @include('ui._quest_button',[
                    'route' => route('ui.info'),
                    'image' => asset('images/info.png'),
                    'title' => 'Game Info',
                    'percentComplete' => '0',
                    'progressMessage' => 'What to do...',
                    'color' => 'default'
                ])

            </div>
        </div>
    </div>
@stop