@extends('layouts.admin', ['title' => 'Clue - Admin'])

@section('content')

    <div class="container">
        <div class="row">
            {!! Breadcrumbs::render("admin.siteMessages") !!}
        </div>
        <div class="row">
            <div class="col-12">
                @include('admin._alert')
                <h1>Site Messages</h1>
                @foreach($messages as $key => $message)
                    <div class="row">
                        <div class="col-12">
                            {{ html()->form('POST', route('admin.siteMessages.update', [$key]))->open() }}
                                <h2>{{ title_case(str_replace('_',' ',$key)) }}</h2>
                                <p class="lead">{{ $message['description'] }}</p>

                                @if(!empty($message['vars']))
                                    <div class="form-text">
                                        You can place the following strings within the text and thye will be replaced by the
                                        variable value from the database. Kind of like a shortcode in wordpress.
                                    </div>
                                    <ul class="list-unstyled">
                                        @foreach($message['vars'] as $var => $desc)
                                            <li><code>||{{ $var }}||</code> : {{ $desc }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                {{ html()->textarea($key, $message['message'])->class('form-control')->rows($message['rows']) }}
                                @if($message['markdown'])
                                    <div class="form-text">
                                        <small>Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</small>
                                    </div>
                                @endif
                                <button class="btn-primary btn mt-3">Save</button>
                            {{ html()->form()->close() }}
                        </div>
                    </div>
                    <hr>
                @endforeach
            </div>
        </div>
    </div>

@endsection