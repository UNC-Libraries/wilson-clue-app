@extends('layouts.admin', ['title' => 'Clue - Admin'])

@section('content')

    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render("admin.$model_name.edit",$model) !!}
        </div>
        @include('partials._delete_form', ['route' => ["admin.$model_name.destroy",$model->id], 'message' => empty($delete_message) ? null : $delete_message])
        <div class="row">
            <div class="col-12">
                @include('admin._form_errors')
                <h1>{{ $page_title ?? 'Edit '.$model_name }}</h1>

                @hasSection('model_edit_inputs')
                    @yield('model_edit_inputs')
                @else
                    {!! Form::model($model, array('route'=> ["admin.$model_name.update",$model->id], 'method'=>'PUT', 'files' => true)) !!}
                        @include("$model_name._inputs", [$model_name => $model])
                    {!! Form::close() !!}
                @endif

            </div>
        </div>
    </div>

@endsection