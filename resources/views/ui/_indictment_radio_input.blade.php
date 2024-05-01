<div class="flex-radio">
    {!! Form::radio($model, $id, $selected, ['id' => $model.$id, 'data-full-name' => $name]) !!}
    <label for="{{ $model.$id }}">
        @if($model == 'location')
            <svg width="100%" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" version="1.1">
                <use xlink:href="#baseMap"></use>
                <use xlink:href="#{{ $image }}" class="map-base"></use>
            </svg>
        @else
            <img class="img-fluid" src="{{ asset($image) }}">
        @endif
        <span class="fa fa-5x fa-check selected"></span>
    </label>
    <p class="small text-center">{{ $name }}</p>
</div>