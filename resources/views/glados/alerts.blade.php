<ul class="list-unstyled">
@foreach($game->alerts as $alert)
    <li>
        <em>{{ $alert->message }}</em>
        <small>
            @include('partials._delete_form', ['route' => ['admin.game.alert.destroy', $game->id, $alert->id], 'class' => 'btn-xs'])
        </small>
    </li>
@endforeach
</ul>
