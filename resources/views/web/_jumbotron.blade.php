<section class="main-section" id="main">
    <div class="container">
        <div class="row clue-jumbotron">
            <div class="col-xl-6 col-sm-12 clue-banner">
                <span class="site-intro">Wilson Library presents...</span>
                <h1>C<small>lue</small></h1>
                <span class="site-catchphrase text-end">A live action mystery event</span>
            </div>
            <div class="col-sm-12 col-xl-6 character-image-div">
                <div class="row">
                    @foreach($suspects as $suspect)
                        <div class="col-4 col-sm-2 col-xl-4">
                            <a href="#{{ $suspect->machine }}" class="char-overlay charnav" data-name="{{ $suspect->name }}" data-main-target="suspects">
                                <img class="clip-circle" src="{{ asset($suspect->face_image) }}" alt="{{ $suspect->name }}">
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-12 text-end">character images by: Ben Pennell</div>
                </div>
            </div>
        </div>

        <div class="row">
            @if($game && $game->registration)
                @include('web._special_notice')
            @elseif($game)
                @include('web._registration_closed')
            @else
                @include('web._site_alert')
            @endif
        </div>

        <div class="row">
            <div class="col-12 col-md-5">
                <div class="row site-nav" role="navigation">
                    <div class="col-12 col-sm-6 col-md-12">
                        <a href="#process" class="scrollnav"><span class="clue-icon clue-icon-process"></span>Process</a>
                    </div>
                    <div class="col-12 col-sm-6 col-md-12">
                        <a href="#suspects" class="scrollnav"><span class="clue-icon clue-icon-suspect"></span>Suspects</a>
                    </div>
                    <div class="col-12 col-sm-6 col-md-12">
                        <a href="#sia" class="scrollnav"><span class="clue-icon clue-icon-shield"></span>The SIA</a>
                    </div>
                    <div class="col-12 col-sm-6 col-md-12">
                        <a href="#archive" class="scrollnav"><span class="clue-icon clue-icon-archive"></span>Archive</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-7">
                @include('web._featured_images')
            </div>
        </div>
    </div>
</section>