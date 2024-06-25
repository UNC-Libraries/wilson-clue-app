<nav class="navbar navbar-inverse fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#gamenav" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ route('ui.index') }}"><span class="fa fa-home"></span></a>
        </div>
        <div class="collapse navbar-collapse" id="gamenav">
            <ul class="nav navbar-nav">
                <li><a href="{{ route('ui.map') }}">Wilson Map</a></li>
                <!--<li><a href="{{ route('ui.dna') }}">Ghost DNA</a></li>-->
                <li><a href="{{ route('ui.indictment') }}">Indictment</a></li>
                <li><a href="{{ route('player.logout') }}">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>