@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    @include('partials._maps')
    <div class="container-fluid">
        @include('ui._quest_header', [
            'img_src' => asset('images/envelope_tiny.png'),
            'quest_title' => 'Evidence Room',
            'quest_subtitle' => $game->evidenceLocation->name,
            'color' => 'default',
            'map_section' => $game->evidenceLocation->map_section,
            'map_color' => 'orange',
            'text' => trans('ui.evidence')
        ])
        <div class="row">
            <div class="col-12">
                <h2>Case File Items</h2>
                @foreach($game->case_file_items as $key => $item)
                    <button type="button" class="btn btn-primary btn-lg btn-block" data-bs-toggle="modal" data-bs-target="#cfModal{{ $key }}">
                        {{ $item->title }}
                    </button>
                @endforeach
            </div>
        </div>
        @if(!$team->indictment_made)
            <div class="row">
                <div class="col-12">
                    <h2>Evidence Items</h2>
                    <div class="question-div">
                        <div class="col-12 text-center">
                            <div id="evidenceForm-response" class="question-response"></div>
                        </div>
                        {{ html()->form('POST', route('ui.set.evidence', ))->class('evidence-form')->id('evidenceForm')->open() }}
                        <div class="form-group">
                            {{ html()->label('Select the Collection Item', 'evidence') }}
                            {{ html()->select('evidence', $game->evidence->pluck('title', 'id')->all(), $team->evidence ? $team->evidence->id : null)->class('form-control') }}
                        </div>
                        <input type="submit" class="btn btn-secondary" value="Submit">
                        {{ html()->form()->close() }}
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <h4 class="modal-title" id="cfModal{{ $key }}Label">{{ $item->title }}</h4>
                    </div>
                    <div class="modal-body">
                        {!! app(Parsedown::class)->text($item->text) !!}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection