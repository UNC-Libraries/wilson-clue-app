@include('partials._maps')
<div class="row">
    @foreach($models->sortBy('floor') as $location)
        <div class="col-6 col-sm-6 col-md-4">
            <div class="card card-body text-center">
                <p class="lead">{{ $location->name }}</p>
                <svg width="100%" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" version="1.1">
                    <use xlink:href="#baseMap"></use>
                    <use xlink:href="#{{ $location->map_section }}" class="map-base"></use>
                </svg>
                <p>Floor {{ $location->floor }}</p>
                <a href="{{ route('admin.location.edit', $location->id) }}" class="btn btn-primary btn-sm">
                    <span class="fa fa-edit"></span> Edit
                </a>
            </div>
        </div>
    @endforeach
</div>
