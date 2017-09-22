@foreach($views as $v)
    <p><strong>{{ $v['name'] }}</strong></p>
    <div class="progress">
        <div class="progress-bar"
             role="progressbar"
             aria-valuenow="{{ $v['count'] }}"
             aria-valuemin="0"
             aria-valuemax="{{ $total }}"
             style="min-width: 2em; width: {{ $v['percent'] }}%">
            {{ $v['percent'] }}%
        </div>
    </div>
@endforeach