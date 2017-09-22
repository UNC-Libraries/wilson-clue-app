<!--Solution Modal -->
<div class="modal fade" id="gameOptions" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Options</h4>
            </div>
            <div class="modal-body">
                {!! Form::model($game, ['route'=> ['admin.game.update',$game->id], 'method'=>'PUT']) !!}

                @include('game.input_groups.options')

                <button type="submit" class="btn btn-primary">Submit</button>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>