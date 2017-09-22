@extends('layouts.master', ['title' => 'Clue - Admin'])

@section('css')
    <link href="{{ asset('css/web.css') }}" rel="stylesheet" type="text/css" >
@stop

@section('main.content')
    <div class="container-fluid">
        @include('web._login_form')
    </div>
@stop