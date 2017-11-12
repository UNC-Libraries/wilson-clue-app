<!DOCTYPE html>
<html>
    <head>
        <title>Error 404</title>

        @include('errors._error_styles')
    </head>
    <body>
        <div class="container">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" viewBox="0 0 100 100" version="1.1" x="0px" y="0px">
                <title>ghosty frowning</title>
                <desc>Created with Sketch by https://thenounproject.com/PeterEmil/</desc>
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <g fill="#000000">
                        <g transform="translate(10.000000, 5.000000)">
                            <path d="M34.92717,91.2354201 C41.7583008,91.2270508 41.919549,86.2139194 45.1581603,86.1675846 C48.7600878,86.1160519 51.752624,89.1439322 56.9176444,89.2224689 C63.4487305,89.3217773 65.1699219,84.1791992 68.2221257,85.8911871 C75.7385762,90.1071808 78.7089844,89.5058594 75.5984789,79.5309117 C71.8246377,67.4287413 78,61.8270061 78,44.5 C78,19.5 64.8528137,0 40,0 C15.1471863,0 2,19.5 2,44.5 C2,61.865299 9.16552734,70.5302734 4.4174785,79.6619448 C0.695684074,86.8198753 -0.420898438,93.1152344 19.4775391,84.1650391 C24.9484083,81.7042756 29.9925478,91.2414659 34.92717,91.2354201 Z M34.92717,87.2354201 C40.1069336,87.1508789 41.9104016,82.2483289 45.0375977,82.21875 C48.7658003,82.1834864 51.6586914,85.5883789 56.7734375,85.5175781 C61.8881836,85.4467773 65.0029297,80.2119141 68.0272751,81.8330483 C75.8906363,86.048031 72.3029137,81.5926605 71.1809363,78.1067845 C68.1845703,68.7973633 73,61.1649077 73,43 C73,20.5 61.09139,3 40,3 C16.90861,3 5,20.4999996 5,43 C5,61.2315539 11.5486901,69.8671477 8.19524347,78.1755172 C5.67860718,84.4106379 5.25048828,87.3847656 18.2983398,81.1645508 C25.0108466,77.9645424 29.8470206,87.3183355 34.92717,87.2354201 Z"/>
                        </g>
                        <path d="M41.75,68.0019531 C43.7734444,68.0019531 46.6101021,69.8508301 49.9824219,69.8508301 C52.491683,69.8508301 55.5392303,68.0019531 57.75,68.0019531 C58.75,68.0019531 58.75,70.0019531 57.75,70.0019531 C55.9473048,70.0019531 53.02719,71.8508301 49.9824219,71.8508301 C46.995592,71.8508301 43.8805641,70.0019531 41.75,70.0019531 C40.75,70.0019531 40.75,68.0019531 41.75,68.0019531 Z" transform="translate(49.750000, 69.926392) scale(1, -1) rotate(-10.000000) translate(-49.750000, -69.926392) "/>
                        <path d="M59.8388672,50.7248514 C56.5783209,50.2648859 54.6982985,48.4233404 54.6982985,45.5842826 C54.6982985,42.7452249 56.0204638,40.3313895 59.8388672,40.4437139 C63.6572706,40.5560384 64.9794359,42.7452249 64.9794359,45.5842826 C64.9794359,48.4233404 63.0994135,51.1848168 59.8388672,50.7248514 Z M43.5141602,39.0785353 C39.382376,39.7943556 37,42.6602573 37,47.0785353 C37,51.4968133 38.675456,55.25334 43.5141602,55.0785353 C48.3528643,54.9037306 50.0283203,51.4968133 50.0283203,47.0785353 C50.0283203,42.6602573 47.6459444,38.362715 43.5141602,39.0785353 Z"/>
                        <path d="M56.9637418,35.4245657 C54.5399817,36.0740102 54.777998,37.5899052 53.3459413,39.0068778 C51.9138847,40.4238504 51.7283143,38.7653666 52.8283033,37.0750262 C53.9282922,35.3846858 53.2462644,33.3148322 56.1872846,32.5267882 C59.0723407,31.7537397 64.5364277,32.5746965 66.3329781,33.4839591 C68.1295286,34.3932217 68.3163371,36.0563262 66.8506162,35.4158108 C65.3848954,34.7752953 60.2211525,34.5517451 56.9637418,35.4245657 Z" transform="translate(59.974356, 35.880686) rotate(10.000000) translate(-59.974356, -35.880686) "/>
                    </g>
                </g>
            </svg>
            <div class="content">
                <div class="title">THIS INVESTIGATION HAS ENDED!!</div>
                <p style="font-size: 2em;">Please report to the lobby!</p>
                <p style="font-size: 1em;"><a href="{{ route('web.index') }}">Back to the Clue homepage</a></p>
            </div>

        </div>
    </body>
</html>