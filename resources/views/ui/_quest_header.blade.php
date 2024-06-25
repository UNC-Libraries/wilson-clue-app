<div class="quest-header row {{ $color ?? '' }}">
    <div class="col-5">
        <div class="quest-map">
            <svg width="100%" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" version="1.1">
                <use xlink:href="#baseMap"></use>
                <use xlink:href="#{{ $map_section }}" class="map-{{ $map_color ?? 'default'}}"></use>
            </svg>
        </div>
    </div>
    <div class="col-7 text-end">
        <img src="{{ $img_src }}" class="quest-header-image">
        <div class="quest-title">
            {{ $quest_title }}
        </div>
        <div class="quest-subtitle">
            {{ $quest_subtitle ?? '' }}
        </div>
    </div>
    <div class="col-12" style="margin-top: 20px;">
        @if(empty($text))
            @include('ui._page_alerts', ['color' => empty($color) ? 'default' : $color, 'checkUrl' => route('ui.status.quest',['id' => $quest_id])])
        @else
            <p class="lead">{{ $text }}</p>
        @endif
    </div>
</div>
