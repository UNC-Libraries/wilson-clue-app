@extends('layouts.asset_index', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'question',
])

@section('model_list')

<div class="row">
    {!! Form::open(['route' => 'admin.question.index', 'id' => 'questionFilterForm', 'method' => 'get']) !!}
        <div class="form-group col-xs-4">
            {!! Form::label('location_id','Filter By Location') !!}
            {!! Form::select('location_id',$locations->pluck('name','id'), $location, array('placeholder' => 'Select a location', 'class' => 'form-control auto-submit', 'data-target'=>'#questionFilterForm')) !!}
        </div>

        <div class="form-group col-xs-4">
            {!! Form::label('game_id','Filter By Game') !!}
            {!! Form::select('game_id',$games->pluck('name','id'), $game, array('placeholder' => 'Select a game', 'class' => 'form-control auto-submit', 'data-target'=>'#questionFilterForm')) !!}
        </div>

        <div class="form-group col-xs-4">
            {!! Form::label('search', 'Search') !!}
            <div class="input-group">
                {!! Form::text('search', $string, array('class'=>'form-control')) !!}
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                </span>
            </div>
        </div>
    {!! Form::close() !!}
</div>
<div class="row">
    <div class="col-xs-12">
        @include('question._table_list', ['models' => $questions])
    </div>
</div>
@stop