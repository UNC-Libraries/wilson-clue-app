<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="100">
    @foreach(str_split($sequence,1) as $position => $strand)
        @include('partials.dna._'.$strand, ['position' => 34 + ($position * 20)])
    @endforeach
    <rect x="30" y="20" width="124" height="8" class="dna-top"></rect><rect x="30" y="86" width="124" height="8" class="dna-bottom"></rect>
</svg>

