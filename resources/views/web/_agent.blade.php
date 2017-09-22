<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
    <div class="agent-card text-center">
        <div class="agent-title">{{ $agent->full_name }}</div>
        {!! Html::image($agent->src, $agent->full_name, array('class'=>'clip-circle circle-light')) !!}
        <p>{{ $agent->title }}</p>
        <dl>
            <dt>Location</dt>
            <dd>{{ $agent->location }}</dd>
        </dl>
    </div>
    <script type="application/ld+json">
        {
          "@context": "http://schema.org",
          "@type": "Person",
          "image": "{{$agent->src}}",
          "name": "{{ $agent->full_name }}"
        }
    </script>
</div>