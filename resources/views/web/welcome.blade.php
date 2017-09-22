@extends('layouts.web')

@section('content')
    @include('web._jumbotron')
    @include('web._process')
    @include('web._suspects')
    @include('web._sia')
    @include('web._archive', ['colors' => ['red','green','orange']])
@stop