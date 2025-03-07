<section class="light-section special-elite" id="suspects">
    <div class="container char-content">
        <div class="row">
            <div class="col-12">
                <div id="suspect-main" class="char-content">
                    <h3>Can you solve the mystery?</h3>
                    <p>One of these suspects is not like the other. He/she/it is a GHOST!</p>
                    <!--<p>Only the most savvy investigators will be able to differentiate friend from foe...</p>-->
                </div>
            </div>
        </div>
    </div>

    @foreach($suspects as $suspect)
    <div class="char-panel" id="{{$suspect->machine}}-panel">
        <div class="container">
            <div class="row char-content">
                <div class="col-12">
                    <h3 id="{{$suspect->machine}}">{{$suspect->name}}</h3>
                    <div class="row">
                        <div class="col-sm-4 d-none">
                            <img src="{{ asset($suspect->card_image) }}" class=" img-fluid char-card" alt="{{$suspect->machine}}-card">
                        </div>
                        <div class="col-sm-8">
                            <div class="bio">
                                <h4>Bio:</h4>
                                <p>{!! app(Parsedown::class)->text($suspect->bio) !!}</p>
                            </div>
                            <blockquote class="blockquote-reverse">
                                <p>{{$suspect->quote}}</p>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="container text-end" id="suspect-header">
        <h2>The Suspects</h2>
    </div>
    <div class="container">
        <div id="suspect-nav">
            <div class="container">
                <div class="row character-image-div">
                @foreach($suspects as $suspect)
                    <div class="col-sm-2 col-4">
                        <a href="#{{$suspect->machine}}" class="char-overlay charnav" data-name="{{$suspect->name}}" data-main-target="suspects">
                            <img class="clip-circle circle-dark" src="{{ asset($suspect->face_image) }}" alt="{{$suspect->name}}">
                        </a>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
</section>