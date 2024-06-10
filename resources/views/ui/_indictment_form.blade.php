@if(!empty($warnings))
<div class="alert alert-warning" role="alert">
    <ul>
    @foreach($warnings as $w)
    @if($w['type'] == 'quest')
        <li>Complete the task in the {{ $w['location']->name }} to interrogate {{ $w['suspect']->name }}.</li>
    @else
        <li>{{ $w['text'] }}</li>
    @endif
    @endforeach
    </ul>
</div>
@endif


<h3>Click on the icons to make your indictment.</h3>
<p>You will need 1 suspect, 1 location, and 1 collection item.</p>

{{ html()->form('POST', route('ui.set.indictment'))->id('indictmentForm')->open() }}
    <legend>Who is the ghost?</legend>
    <div class="flex-form-group">
        @foreach($game->quests->pluck('suspect')->sortBy('machine') as $s)
            @include('ui._indictment_radio_input',['model' => 'suspect', 'id' => $s->id, 'name' => $s->name, 'selected' => $team->suspect ? $team->suspect->id == $s->id : false, 'image' => asset($s->face_image)])
        @endforeach
    </div>
    <legend>Where is their portal?</legend>
    <div class="flex-form-group">
        @foreach($game->quests->pluck('location')->sortBy('floor') as $l)
            @include('ui._indictment_radio_input',['model' => 'location', 'id' => $l->id, 'name' => $l->name, 'selected' => $team->location ? $team->location->id == $l->id : false, 'image' => $l->map_section, 'text' => $l->name])
        @endforeach
    </div>
    <legend>Which item did it touch?</legend>
    <div class="flex-form-group">
        @foreach($game->evidence->sortBy('title') as $e)
            @include('ui._indictment_radio_input',['model' => 'evidence', 'id' => $e->id, 'name' => $e->title, 'selected' => $team->evidence ? $team->evidence->id == $e->id : false, 'image' => $e->src ])
        @endforeach
    </div>
{{ html()->form()->close() }}
<div class="text-center">
    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#indictmentConfirmModal">Submit Your Indictment</button>
</div>

<!-- Modal -->
<div class="modal fade" id="indictmentConfirmModal" tabindex="-1" role="dialog" aria-labelledby="indictmentConfirmModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="indictmentConfirmModalLabel">Are you sure you want to submit the following?</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="indictment-feedback">
                <dl>
                    <dt>Ghost:</dt>
                    <dd id="suspectSelection"></dd>
                    <dt>Portal Location:</dt>
                    <dd id="locationSelection"></dd>
                    <dt>Touched Item:</dt>
                    <dd id="evidenceSelection"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary float-end" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary float-start" id="indictmentSubmit">Confirm</button>
            </div>
        </div>
    </div>
</div>