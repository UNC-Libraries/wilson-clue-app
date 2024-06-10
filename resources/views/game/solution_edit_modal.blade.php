<!--Solution Modal -->
<div class="modal fade" id="gameSolution" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h4 class="modal-title">Solution</h4>
            </div>
            <div class="modal-body">
                {{ html()->form('PUT', route('admin.game.update', [$game->id]))->open() }}
                    @include('game.input_groups.solution')
                    <button type="submit" class="btn btn-primary">Submit</button>
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
</div>