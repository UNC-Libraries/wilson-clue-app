<!-- Player Emails -->
<div class="modal fade" id="{{ $modal_id }}Modal" tabindex="-1" role="dialog" aria-labelledby="{{ $modal_id }}Label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="{{ $modal_id }}Label">Player Emails</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary clipboard-btn" data-clipboard-target="#{{$modal_id}}">
                            <i class="fa fa-clipboard"></i>
                            Copy to clipboard
                        </button>
                        <div class="well-sm card card-body" id="{{$modal_id}}">
                            @foreach($teams as $team)
                                @foreach($team->players as $player)
                                    {{ $player->email }};
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>