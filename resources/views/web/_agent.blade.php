<div class="col-md-3 col-sm-4 col-xs-6 col-12">
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