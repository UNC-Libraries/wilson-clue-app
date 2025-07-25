@extends('layouts.master', ['title' => 'Clue - Admin'])

@section('css')
    @vite('resources/assets/sass/web.scss')
@stop

@section('main.content')
    <div class="container-fluid">
        @include('web._login_form')
    </div>
@stop