@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    @include('partials._maps')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if($team->indictment_made)
                    @include('ui._indictment_complete')
                @else
                    @include('ui._indictment_form')
                @endif
            </div>
        </div>
    </div>
@endsection