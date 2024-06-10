@extends('layouts.master', ['title' => 'Clue - Presented by Wilson Library!'])

@section('css')
    <!-- CSS -->
    <link href="{{ asset('css/ui.css') }}" rel="stylesheet" type="text/css" >
@endsection


@section('main.content')

    @include('ui._nav')
    @yield('content')

    <div class="modal fade" tabindex="-1" role="dialog" id="alertModal" data-url="{{ route('ui.alert') }}">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="margin-top: -10px;font-size: 3em;"></button>
                    <span id="alertModalBody"></span>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@endsection