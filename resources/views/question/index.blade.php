@extends('layouts.asset_index', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'question',
])

@section('model_list')

<div class="row">
    {{ html()->form('GET', route('admin.question.index'))->id('questionFilterForm')->class('row')->open() }}
        <div class="form-group col-12 col-sm-4">
            {{ html()->label('Filter By Location', 'location_id')->class('fw-bold mb-1') }}
            {{ html()->select('location_id', $locations->pluck('name', 'id'), $location)->placeholder('Select a location')->class('form-control auto-submit')->data('target', '#questionFilterForm') }}
        </div>

        <div class="form-group col-12 col-sm-4">
            {{ html()->label('Filter By Game', 'game_id')->class('fw-bold mb-1') }}
            {{ html()->select('game_id', $games->pluck('name', 'id'), $game)->placeholder('Select a game')->class('form-control auto-submit')->data('target', '#questionFilterForm') }}
        </div>

        <div class="form-group col-12 col-sm-4">
            {{ html()->label('Search', 'search')->class('fw-bold mb-1')  }}
            <div class="input-group">
                {{ html()->text('search', $string)->class('form-control') }}
                <span class="input-group-btn">
                    <button class="btn btn-secondary" type="submit"><span class="fa fa-search"></span></button>
                </span>
            </div>
        </div>
    {{ html()->form()->close() }}
</div>
<div class="row">
    <div class="col-12">
        @include('question._table_list', ['models' => $questions])
    </div>
</div>
@stop