@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.player.index') !!}
        </div>

        @include('admin._alert')


        <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-3">
                {{ html()->form('GET', route('admin.player.index', ))->open() }}
                <div class="form-group">
                    <label>Search:</label>
                    {{ html()->text('search', $search)->class('form-control') }}
                </div>
                <div class="form-group">
                    <label>Sort By:</label>
                    <div class="row">
                        <div class="col-xs-12 col-sm-7">
                            {{ html()->select('sort_by', $sortOptions, $selectedSort)->class('form-control auto-submit') }}
                        </div>
                        <div class="col-xs-12 col-sm-5">
                            {{ html()->select('sort_order', $sortOrder, $selectedSortOrder)->class('form-control auto-submit') }}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Filter By:</label>
                    <fieldset>
                        <legend>Played</legend>
                        <label class="radio-inline" for="playedBoth">
                            <input
                                    type="radio"
                                    name="played"
                                    id="playedBoth"
                                    value="0"
                                    class="auto-submit"
                                    @if(!$request) checked @endif
                                    @if($request && $request->get('played') != 'yes' && $request->get('played') != 'no') checked @endif
                            > Either
                        </label>
                        <label class="radio-inline" for="playedYes">
                            <input type="radio" name="played" id="playedYes" value="yes" class="auto-submit"
                               @if($request && $request->get('played') == 'yes') checked @endif
                            > Yes
                        </label>
                        <label class="radio-inline" for="playedNo">
                            <input type="radio" name="played" id="playedNo" value="no" class="auto-submit"
                                   @if($request && $request->get('played') == 'no') checked @endif
                            > No
                        </label>
                    </fieldset>
                </div>
                <fieldset>
                    @include('partials._facet_filter',['facetName' => 'non_student', 'facets' => ['1' => 'Non-Student']])
                </fieldset>
                <fieldset>
                    @include('partials._facet_filter',['facetName' => 'manual', 'facets' => ['1' => 'Manually Entered']])
                </fieldset>
                <fieldset>
                    <legend>Games</legend>
                    @include('partials._facet_filter',['facetName' => 'game', 'facets' => $games->pluck('name','id')->all()])
                </fieldset>
                <fieldset>
                    <legend>Class</legend>
                    @include('partials._facet_filter',['facetName' => 'class', 'facets' => $class_options])
                </fieldset>
                <fieldset>
                    <legend>Academic Groups</legend>
                    @include('partials._facet_filter',['facetName' => 'group', 'facets' => $academic_group_options])
                </fieldset>
                {{ html()->form()->close() }}

            </div>
            <div class="col-xs-12 col-sm-8 col-md-9">
                <h1>Players</h1>

                <p>{{ $players->count() }} players found</p>
                <table class="table player-table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Onyen</th>
                        <th>Email</th>
                        <th>PID</th>
                        <th>Class</th>
                        <th>Academic Group</th>
                        <th>Team Count</th>
                        <th>Played</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($players as $player)
                        <tr>
                            <td><a href="{{ route('admin.player.edit', [$player->id]) }}">{{ $player->full_name }}</a></td>
                            <td>{{ $player->onyen }}</td>
                            <td>{{ $player->email }}</td>
                            <td>{{ $player->pid }}</td>
                            <td>{{ $player->class }}</td>
                            <td>{{ $player->academic_group }}</td>
                            <td>{{ $player->teams->count() }}</td>
                            <td>
                                @if($player->checked_in)
                                <span class="fa fa-check text-success"></span>
                                @else
                                <span class="fa fa-times-circle-o text-danger"></span>
                                @endif
                            </td>
                            <td>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
