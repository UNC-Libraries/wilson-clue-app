<div class="modal fade" tabindex="-1" role="dialog" id="importEvidenceRoomModal">
    <div class="modal-dialog" role="document">
        {{ html()->form('POST', route('admin.game.import-evidence-room', [$game->id]))->open() }}
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import Evidence Room From Previous Game</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                {{ html()->label('Filter By Game', 'game_id') }}
                {{ html()->select('game_id', $games->pluck('name', 'id'))->placeholder('Select a game')->class('form-control') }}

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">Close</button>
                <button type="Submit" class="btn btn-primary float-end">Import</button>
            </div>
        </div><!-- /.modal-content -->
        {{ html()->form()->close() }}
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->