<section class="light-section special-elite" id="archive">
    <div class="container">
        <div class="row">
            <div class="col-12 text-end">
                <h2>The Archive</h2>
            </div>

            @foreach($games as $game)
                <div class="col-12 col-xs-6 col-sm-4">
                    <!-- Sideswipe -->
                    <div class="archive-card-wrapper">
                        <div class="archive-card {{ next($colors) == FALSE ? reset($colors) : current($colors) }}">
                            <a href="{{ route('web.archive', $game->id) }}" class="web-link"></a>
                            <div class="img-container">
                                <div class="img" style="background-image: url({{ $game->flickr_start_img }}" aria-label="{{ $game->name }}"></div>
                            </div>
                            <div class="body">
                                <h3 class="game-title">{{ $game->name }}</h3>
                                <span class="game-date">
                                    {{ $game->start_time->format('F jS') }}
                                </span>
                            </div>
                            <div class="desc">
                                <h4>Winning Team</h4>
                                <p>{{$game->winningTeam()->first()->name}}</p>
                            </div>
                            <div class="view-game">
                                View Game
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>