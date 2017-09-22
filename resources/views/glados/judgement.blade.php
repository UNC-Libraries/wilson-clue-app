@foreach($game->quests->where('needs_judgement',true) as $quest)
    @include('game._judge_quest', ['quest' => $quest, 'game' => $game])
@endforeach