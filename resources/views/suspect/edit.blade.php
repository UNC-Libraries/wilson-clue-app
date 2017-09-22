@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.suspect.edit',$suspect) !!}
        </div>

        <div class="row">
            @include('admin._form_errors')

            <div class="col-xs-12 col-sm-8 col-sm-offset-2">
                {!! Form::model($suspect, array('route'=> array('admin.suspect.update',$suspect->id), 'method'=>'PUT', 'files' => true)) !!}

                    {!! Form::token() !!}

                    <div class="row">
                        <!-- Name -->
                        <div class="form-group col-xs-12">
                            {!! Form::label('name', 'Name') !!}
                            {!! Form::text('name', null, array('class'=>'form-control')) !!}
                        </div>

                        <!-- Bio -->
                        <div class="form-group col-xs-12">
                            {!! Form::label('bio', 'Bio') !!}
                            <small>Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</small>
                            {!! Form::textarea('bio', null, array('class'=>'form-control')) !!}
                        </div>

                        <!-- Quote -->
                        <div class="form-group col-xs-12">
                            {!! Form::label('quote','Quote') !!}
                            {!! Form::text('quote', null, array('class'=>'form-control')) !!}
                        </div>

                        <div class="form-group col-xs-12">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop