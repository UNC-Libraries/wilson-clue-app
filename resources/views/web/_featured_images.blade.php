@php
    $featured_images = [
        ['src' => 'https://c2.staticflickr.com/6/5780/30258280034_34d6fe29a6_z.jpg', 'alt' => 'Charlie&#x27;s Angels'],
        ['src' => 'https://c1.staticflickr.com/3/2820/34167730112_86b3ea5f19_z.jpg', 'alt' => 'The Clue Crew'],
        ['src' => 'https://c1.staticflickr.com/3/2809/34325748015_80073fc5eb_z.jpg', 'alt' => 'Rangers'],
        ['src' => 'https://c2.staticflickr.com/2/1615/26579602575_3bbf88f140_z.jpg', 'alt' => 'School Psych interrogates Prof. Plum'],
        ['src' => 'https://c1.staticflickr.com/1/581/22775870742_537dda7ef9_z.jpg', 'alt' => 'RHA Dream Team']
    ];
@endphp

<div id="featured-images-carousel" class="carousel slide" data-ride="carousel">
    <!-- Indicators -->
    <ol class="carousel-indicators">
        @foreach ($featured_images as $index => $image)
        <li data-target="#featured-images-carousel" data-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></li>
        @endforeach
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
        @foreach ($featured_images as $index => $image)
        <div class="item {{ $index === 0 ? 'active' : '' }}">
            <img src="{{ $image['src'] }}" alt="{{ $image['alt'] }}">
        </div>
        @endforeach
    </div>

    <!-- Controls -->
    <a class="left carousel-control" href="#featured-images-carousel" role="button" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="right carousel-control" href="#featured-images-carousel" role="button" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>