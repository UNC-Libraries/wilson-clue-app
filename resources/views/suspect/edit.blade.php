@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.suspect.edit',$suspect) !!}
        </div>

        <div class="row">
            @include('admin._form_errors')

            <div class="col col-xs-8 offset-sm-2">
                {{ html()->modelForm($suspect, 'PUT', route('admin.suspect.update', [$suspect->id]))->acceptsFiles()->open() }}
                    {{ html()->token() }}

                    <div class="row">
                        <!-- Name -->
                        <div class="form-group col-xs-12">
                            {{ html()->label('Name', 'name')->class('fw-bold mb-1') }}
                            {{ html()->text('name')->class('form-control') }}
                        </div>

                        <!-- Bio -->
                        <div class="form-group col-xs-12">
                            {{ html()->label('Bio', 'bio')->class('fw-bold mb-1 mt-2')  }}
                            <small>Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</small>
                            {{ html()->textarea('bio')->class('form-control') }}
                        </div>

                        <!-- Quote -->
                        <div class="form-group col-xs-12">
                            {{ html()->label('Quote', 'quote')->class('fw-bold mb-1 mt-2') }}
                            {{ html()->text('quote')->class('form-control') }}
                        </div>

                        <div class="form-group col mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>

                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
@stop