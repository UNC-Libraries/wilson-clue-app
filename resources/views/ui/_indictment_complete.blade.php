<div class="alert alert-info text-center" role="alert">
    <h5>You can still score points!</h5>
    <p>Keep answering questions<!-- and searching for ghost DNA -->until the time runs out!</p>
</div>
<div class="card card-body well-sm">
    <h3>Your indictment</h3>
    <p><strong>Ghost:</strong> {{  $team->suspect->name }}</p>
    <p><strong>Portal:</strong> {{ $team->location->name }}</p>
    <p><strong>Evidence:</strong> {{ $team->evidence->title }}</p>
</div>