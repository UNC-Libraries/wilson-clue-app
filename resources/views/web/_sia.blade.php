<section class="main-section" id="sia">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>The Supernatural Investigation Agency (S.I.A)</h2>
                <div class="row">
                    @foreach($agents['active']->sortBy('last_name') as $agent)
                        @include('web._agent',['agent'=>$agent])
                    @endforeach
                </div>

                <h3>Retired Agents</h3>
                <div class="row">
                    @foreach($agents['retired']->sortBy('last_name') as $agent)
                        @include('web._agent',['agent'=>$agent])
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>