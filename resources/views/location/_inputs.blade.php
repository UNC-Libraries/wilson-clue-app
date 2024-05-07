@include('partials._maps')
<div class="row">
    <!-- Name -->
    <div class="form-group col-12 col-sm-8 mb-3">
        {!! Form::label('name', 'Name', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('name', null, array('class'=>'form-control')) !!}
    </div>

    <!-- Floor -->
    <div class="form-group col-12 col-sm-4 mb-3">
        {!! Form::label('floor', 'Floor', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::select('floor', array('1' => '1', '2' => '2', '3' => '3', '4' => '4'), $location->floor, array('placeholder' => 'Select a floor', 'class' => 'form-control')) !!}

    </div>

    <!-- Map section -->
    @foreach($mapSections as $section)
        <div class="form-group col-6 col-xs-2">
            <div class="form-check center-radio-button">
                <label>
                    <svg width="100%" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" version="1.1">
                        <use xlink:href="#baseMap"></use>
                        <use xlink:href="#{{ $section }}" class="map-base"></use>
                    </svg>
                    {!! Form::radio('map_section',$section) !!}
                </label>
            </div>
        </div>
    @endforeach

    <div class="form-group col-12">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>