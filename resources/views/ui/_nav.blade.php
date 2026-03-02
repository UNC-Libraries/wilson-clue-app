<nav class="navbar navbar-inverse fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#gamenav" aria-controls="gameNavigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand ms-1" href="{{ route('ui.index') }}"><span class="fa fa-home"></span></a>
            <div class="collapse navbar-collapse" id="gamenav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-item" href="{{ route('ui.map') }}">Wilson Map</a></li>
                    <!--<li class="nav-item"><a class="nav-item" href="{{ route('ui.dna') }}">Ghost DNA</a></li>-->
                    <li class="nav-item"><a class="nav-item" href="{{ route('ui.indictment') }}">Indictment</a></li>
                    <li class="nav-item"><a class="nav-item" href="{{ route('player.logout') }}">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>