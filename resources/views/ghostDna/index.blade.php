@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.ghostDna.index') !!}
        </div>

        <div class="row">
            <div class="col-xs-12">
                <h1>Ghost DNA</h1>
                @include('admin._alert')
            </div>
            <div class="col-xs-12 col-md-4">
                @include('admin._form_errors')
                {{ html()->form('POST', route('admin.ghostDna.store', ))->open() }}
                    <div class="form-group">
                        {{ html()->label('Add a Sequence', 'sequence') }}
                        {{ html()->text('sequence')->class('form-control')->maxlength('6')->minlength('6')->attribute('aria-describedby', 'sequenceHelpBlock') }}
                        <div id="sequenceHelpBlock" class="help-block">6 characters long, only consisting of g, h, s, or t</div>
                    </div>
                    <button type="submit" class="btn btn-success">Add</button>

                {{ html()->form()->close() }}
            </div>
            <div class="col-xs-12 col-md-8">
                <p><strong>Current Pairs</strong></p>
                <table class="table" role="presentation">
                    <tbody>
                        @foreach($pairs as $pair)
                            <tr>
                                @foreach($pair as $dna)
                                    <td>{{ $dna->sequence }}</td>
                                @endforeach
                                <td>
                                    @include('partials._delete_form', ['route' => ['admin.ghostDna.destroy',$dna->pair]])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@stop
