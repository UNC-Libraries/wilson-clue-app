@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.suspect.edit',$suspect) !!}
        </div>

        <div class="row">
            @include('admin._form_errors')

            <div class="col col-xs-8 offset-sm-2">
                {!! Form::model($suspect, array('route'=> array('admin.suspect.update',$suspect->id), 'method'=>'PUT', 'files' => true)) !!}

                    {!! Form::token() !!}

                    <div class="row">
                        <!-- Name -->
                        <div class="form-group col-12">
                            {!! Form::label('name', 'Name', ['class' => 'fw-bold mb-1']) !!}
                            {!! Form::text('name', null, array('class'=>'form-control')) !!}
                        </div>

                        <!-- Bio -->
                        <div class="form-group col-12">
                            {!! Form::label('bio', 'Bio', ['class' => 'fw-bold mb-1 mt-2']) !!}
                            <small>Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</small>
                            {!! Form::textarea('bio', null, array('class'=>'form-control')) !!}
                        </div>

                        <!-- Quote -->
                        <div class="form-group col-12">
                            {!! Form::label('quote','Quote', ['class' => 'fw-bold mb-1 mt-2']) !!}
                            {!! Form::text('quote', null, array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group col mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop