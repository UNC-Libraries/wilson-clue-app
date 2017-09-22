@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    @include('partials._maps')
    <div class="container-fluid">
        @include('ui._quest_header', [
            'img_src' => asset('images/envelope_tiny.png'),
            'quest_title' => 'Evidence Room',
            'quest_subtitle' => $game->evidenceLocation->name,
            'color' => 'default',
            'map_section' => $game->evidenceLocation->mapSection->name,
            'map_color' => 'default',
            'text' => trans('ui.evidence')
        ])
        <div class="row">
            <div class="col-xs-12">
                <h2>Case File Items</h2>
                @foreach($game->case_file_items as $key => $item)
                    <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#cfModal{{ $key }}">
                        {{ $item->title }}
                    </button>
                @endforeach
            </div>
        </div>
        @if(!$team->indictment_made)
            <div class="row">
                <div class="col-xs-12">
                    <h2>Evidence Items</h2>
                    <div class="question-div">
                        <div class="col-xs-12 text-center">
                            <div id="evidenceForm-response" class="question-response"></div>
                        </div>
                        {!! Form::open(['route' => ['ui.set.evidence'], 'class' => 'evidence-form', 'id' => 'evidenceForm']) !!}
                        <div class="form-group">
                            {!! Form::label('evidence', 'Select the Collection Item') !!}
                            {!! Form::select('evidence', $game->evidence->pluck('title','id')->all(), $team->evidence ? $team->evidence->id : null, ['class' => 'form-control']) !!}
                        </div>
                        <input type="submit" class="btn btn-default" value="Submit">
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        @endif
    </div>

    @foreach($game->case_file_items as $key => $item)
        <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cfModal{{ $key }}Label" id="cfModal{{ $key }}">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="cfModal{{ $key }}Label">{{ $item->title }}</h4>
                    </div>
                    <div class="modal-body">
                        @markdown($item->text)
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection