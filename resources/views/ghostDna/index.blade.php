@extends('layouts.admin', ['title' => 'Clue - Game Admin'])

@section('content')
    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render('admin.ghostDna.index') !!}
        </div>

        <div class="row">
            <div class="col-12">
                <h1>Ghost DNA</h1>
                @include('admin._alert')
            </div>
            <div class="col-12 col-sm-4">
                @include('admin._form_errors')
                {!! Form::open(['route'=>['admin.ghostDna.store']]) !!}
                    <div class="form-group">
                        {!! Form::label('sequence', 'Add a Sequence') !!}
                        {!! Form::text('sequence', null, array('class'=>'form-control', 'maxlength'=>'6', 'minlength'=>'6', 'aria-describedby' => 'sequenceHelpBlock')) !!}
                        <div id="sequenceHelpBlock" class="form-text">6 characters long, only consisting of g, h, s, or t</div>
                    </div>
                    <button type="submit" class="btn btn-success">Add</button>

                {!! Form::close() !!}
            </div>
            <div class="col-12 col-sm-8">
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
