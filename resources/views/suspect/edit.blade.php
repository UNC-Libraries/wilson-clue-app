@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.suspect.edit',$suspect) !!}
        </div>

        <div class="row">
            @include('admin._form_errors')

            <div class="col-xs-12 col-sm-8 col-sm-offset-2">
                {{ html()->modelForm($suspect, 'PUT', route('admin.suspect.update', [$suspect->id]))->acceptsFiles()->open() }}

                    {{ html()->token() }}

                    <div class="row">
                        <!-- Name -->
                        <div class="form-group col-xs-12">
                            {{ html()->label('Name', 'name') }}
                            {{ html()->text('name')->class('form-control') }}
                        </div>

                        <!-- Bio -->
                        <div class="form-group col-xs-12">
                            {{ html()->label('Bio', 'bio') }}
                            <small>Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</small>
                            {{ html()->textarea('bio')->class('form-control') }}
                        </div>

                        <!-- Quote -->
                        <div class="form-group col-xs-12">
                            {{ html()->label('Quote', 'quote') }}
                            {{ html()->text('quote')->class('form-control') }}
                        </div>

                        <div class="form-group col-xs-12">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>

                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
@stop