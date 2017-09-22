@extends('layouts.admin', ['title' => 'Clue - Admin'])

@section('content')

    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render("admin.$model_name.index") !!}
        </div>
        <div class="row">
            <div class="col-xs-12">
                @include('admin._alert')
                <h1>
                    {{ $page_title or ucfirst(str_plural($model_name)) }}
                    <a href="{{ $create_route or route("admin.$model_name.create") }}" class="btn btn-success btn-sm pull-right">
                        <span class="fa fa-plus-circle"></span> Add New
                    </a>
                </h1>
                @hasSection('model_list')
                    @yield('model_list')
                @else
                    @include("$model_name._list", ['models' => $models])
                @endif
            </div>
        </div>
    </div>

@endsection