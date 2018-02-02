@extends('layouts.master', ['title' => 'Clue - Presented by Wilson Library!'])

@section('css')
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.1.0/css/swiper.min.css">
    <link href="{{ asset('css/web.css') }}" rel="stylesheet" type="text/css" >
@endsection


@section('main.content')

    @yield('content')

    @include('web._footer')

@endsection

@section('additional_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.1.0/js/swiper.min.js"></script>
    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@id": "http://library.unc.edu/wilson/",
            "@type": "Organization",
                "parentOrganization": {
                    "@type": "Organization",
                    "name": "UNC Chapel Hill Libraries",
                    "parentOrganization": {
                        "@type": "CollegeOrUniversity",
                        "name": "University of North Carolina at Chapel Hill"
                    }
                },
                "logo": "http://library.unc.edu/wp-content/themes/responsiveUNCLib/images/wilsonbanner.png",
                "url": "http://library.unc.edu/wilson/",
                "name": "Louis Round Wilson Library",
                "sameAs": "https://en.wikipedia.org/wiki/Louis_Round_Wilson_Library"
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@id": "homepage",
            "@type": "WebPage",
            "keywords": "wilson,library,clue,unc",
            "publisher":
            {
                "@id":"http://library.unc.edu/wilson/"
            }
        }
    </script>
@endsection