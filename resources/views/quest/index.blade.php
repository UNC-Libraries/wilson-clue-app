<h1>Game Quests</h1>

<p>{{ $game->name }}</p>

<ul>
    @foreach($game->quests as $quest)
        <li>{{ $quest->id }}</li>
    @endforeach
</ul>

