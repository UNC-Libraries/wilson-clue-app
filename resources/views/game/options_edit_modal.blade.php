<!--Solution Modal -->
<div class="modal fade" id="gameOptions" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Options</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ html()->modelForm($game, 'PUT', route('admin.game.update', [$game->id]))->open() }}

                @include('game.input_groups.options')

                <button type="submit" class="btn btn-primary">Submit</button>

                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
</div>