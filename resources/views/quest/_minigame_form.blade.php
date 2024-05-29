<legend>Minigame Images <small>(drag and drop to add/remove)</small></legend>
{{ html()->hidden('minigame_image_list', implode(',', $quest->minigameImages->pluck('id')->all())) }}
<div class="table">
    <div class="table-cell-col-2">
        <div class="row">
            <div class="col-xs-12">
                <h4>Minigame Images <small><span class="drop-count">{{ $quest->minigameImages->count() }}</span> images</small></h4>
                <div class="well" id="minigameImageList">
                    @include('minigameImage._select_list',array('images'=> $quest->minigameImages))
                </div>
            </div>
        </div>
    </div>
    <div class="table-cell-col-2">
        <div class="row">
            <div class="col-xs-12">
                <h4>Available Images</h4>
                <div class="well" id="availableMinigameImages">
                    @include('minigameImage._select_list',array('images'=> $minigameImages))
                </div>
            </div>
        </div>
    </div>
</div>