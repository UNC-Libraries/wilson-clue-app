@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    <!--
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>Ghost DNA</h1>
                <p class="lead">Keep your eyes peeled for ghost dust! You can use your handy "ghost goggles" to identify the sequence.</p>
                @include('ui._dna_form')
            </div>
        </div>
        <div class="row">
            @foreach($sequences as $order => $sequence)
                <div class="col-12 col-xs-6 dna-svg {{ $sequence[0]['collected'] ? 'top' : '' }} {{ $sequence[1]['collected'] ? 'bottom' : '' }}" id="sequence-{{ $sequence[0]['pair'] }}">
                    <h4>Sequence {{ $order + 1 }}</h4>
                    @include('partials.dna._string', ['sequence' => $sequence[0]['sequence']])
                </div>
            @endforeach
            </div>
        </div>
    </div>
    -->
@endsection