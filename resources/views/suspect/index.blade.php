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
            <div class="col-xs-12 col-sm-8 col-sm-offset-2">
                <h2>Suspects</h2>
                @foreach($suspects as $suspect)
                <div class="media">
                    <div class="media-left media-top">
                        <a href="{{ route('admin.suspect.edit',[$suspect->id]) }}">
                            {!! Html::image(asset($suspect->face_image),$suspect->name,array('class'=>'media-object media-object-128')) !!}
                        </a>
                    </div>
                    <div class="media-body">
                        <a href="{{ route('admin.suspect.edit',[$suspect->id]) }}" class="btn btn-primary pull-right"><span class="fa fa-edit"></span></a>
                        <h4 class="media-heading">{{ $suspect->name }}</h4>
                        <div class="row">
                            <div class="col-xs-12">
                                <h5>Bio</h5>
                                @php($Parsedown = new Parsedown())
                                <p>{!! $Parsedown->text($suspect->bio) !!}</p>
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