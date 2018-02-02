@php
    $featured_images = [
        ['src' => 'https://c2.staticflickr.com/6/5780/30258280034_34d6fe29a6_z.jpg', 'alt' => 'Charlie&#x27;s Angels'],
        ['src' => 'https://c1.staticflickr.com/3/2820/34167730112_86b3ea5f19_z.jpg', 'alt' => 'The Clue Crew'],
        ['src' => 'https://c1.staticflickr.com/3/2809/34325748015_80073fc5eb_z.jpg', 'alt' => 'Rangers'],
        ['src' => 'https://c2.staticflickr.com/2/1615/26579602575_3bbf88f140_z.jpg', 'alt' => 'School Psych interrogates Prof. Plum'],
        ['src' => 'https://c1.staticflickr.com/1/581/22775870742_537dda7ef9_z.jpg', 'alt' => 'RHA Dream Team']
    ];
@endphp

<!-- Slider main container -->
<div class="swiper-container" style="height: 427px;">
    <!-- Additional required wrapper -->
    <div class="swiper-wrapper">
        <!-- Slides -->
        @foreach ($featured_images as $index => $image)
        <div class="swiper-slide">
            <img src="{{ $image['src'] }}" alt="{{ $image['alt'] }}">
        </div>
        @endforeach
    </div>
    <!-- If we need pagination -->
    <div class="swiper-pagination swiper-button-white"></div>

    <!-- If we need navigation buttons -->
    <div class="swiper-button-prev">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 27 44"><path d="M0,22L22,0l2.1,2.1L4.2,22l19.9,19.9L22,44L0,22L0,22L0,22z"></svg>
    </div>
    <div class="swiper-button-next">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 27 44"><path d="M27,22L27,22L5,44l-2.1-2.1L22.8,22L2.9,2.1L5,0L27,22L27,22z"></svg>
    </div>
</div>