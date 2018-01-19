<section class="main-section" id="main">
    <div class="container">
        <div class="row clue-jumbotron">
            <div class="col-lg-6 col-md-7 col-sm-12 clue-banner">
                <span class="site-intro">Wilson Library presents...</span>
                <h1>C<small>lue</small></h1>
                <span class="site-catchphrase text-right">A live action mystery event</span>
            </div>
            <div class="col-lg-6 col-md-5 col-sm-12 character-image-div">
                <div class="row">
                    @foreach($suspects as $suspect)
                        <div class="col-md-4 col-sm-2 col-xs-4">
                            <a href="#{{ $suspect->machine }}" class="char-overlay charnav" data-name="{{ $suspect->name }}" data-main-target="suspects">
                                <img class="clip-circle" src="{{ asset($suspect->face_image) }}" alt="{{ $suspect->name }}">
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-xs-12 text-right">character images by: Ben Penell</div>
                </div>
            </div>
        </div>

        <div class="row">
            @if($game && $game->registration)
                @include('web._special_notice')
            @else
                @include('web._site_alert')
            @endif
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-6">
                <div class="row site-nav" role="navigation">
                    <div class="col-xs-12">
                        <a href="#process" class="scrollnav"><span class="clue-icon clue-icon-process"></span>Process</a>
                    </div>
                    <div class="col-xs-12">
                        <a href="#suspects" class="scrollnav"><span class="clue-icon clue-icon-suspect"></span>Suspects</a>
                    </div>
                    <div class="col-xs-12">
                        <a href="#sia" class="scrollnav"><span class="clue-icon clue-icon-shield"></span>The SIA</a>
                    </div>
                    <div class="col-xs-12">
                        <a href="#archive" class="scrollnav"><span class="clue-icon clue-icon-archive"></span>Archive</a>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-6">
                <img class="img-responsive" id="carouselWrapper" src="{{ asset('images/clue-mag.svg') }}">
            </div>
        </div>
    </div>
</section>