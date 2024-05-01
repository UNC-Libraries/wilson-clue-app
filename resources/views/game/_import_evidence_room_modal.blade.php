<div class="modal fade" tabindex="-1" role="dialog" id="importEvidenceRoomModal">
    <div class="modal-dialog" role="document">
        {!! Form::open(['route' => ['admin.game.import-evidence-room',$game->id]]) !!}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Import Evidence Room From Previous Game</h4>
            </div>
            <div class="modal-body">

                {!! Form::label('game_id','Filter By Game') !!}
                {!! Form::select('game_id',$games->pluck('name','id'), null, array('placeholder' => 'Select a game', 'class' => 'form-control')) !!}

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary float-left" data-dismiss="modal">Close</button>
                <button type="Submit" class="btn btn-primary float-right">Import</button>
            </div>
        </div><!-- /.modal-content -->
        {!! Form::close() !!}
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->