@extends('layouts.admin', ['title' => 'Clue - Admin'])

@section('content')

    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render("admin.$model_name.create",$model) !!}
        </div>
        <div class="row">
            <div class="col-xs-12">
                <h1>{{ $page_title ?? "Add new $model_name" }}</h1>
                @include('admin._form_errors')

                {{ html()->modelForm($model, 'POST', route("admin.{$model_name}.store", [$model->id]))->acceptsFiles()->open() }}

                @hasSection('model_create_inputs')
                    @yield('model_create_inputs')
                @else
                    @include("$model_name._inputs", [$model_name => $model])
                @endif

                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>

@endsection