@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.suspect.index') !!}
        </div>

        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12 col-xs-8">
                <h2>Suspects</h2>
                @foreach($suspects as $suspect)
                <div class="d-flex align-items-top mt-2">
                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.suspect.edit',[$suspect->id]) }}">
                            {{ html()->img(asset(asset($suspect->face_image)), $suspect->name)->class('media-object media-object-128') }}
                        </a>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <a href="{{ route('admin.suspect.edit',[$suspect->id]) }}" class="btn btn-primary float-end"><span class="fa fa-edit"></span></a>
                        <h4 class="media-heading">{{ $suspect->name }}</h4>
                        <div class="row">
                            <div class="col-12">
                                <h5>Bio</h5>
                                <p>{!! app(Parsedown::class)->text($suspect->bio) !!}</p>
                                <h5>Quote</h5>
                                <p>{{ $suspect->quote }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
@stop