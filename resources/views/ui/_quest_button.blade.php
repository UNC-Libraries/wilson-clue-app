<div class="media quest-button">
    <a href="{{ $route ?? '' }}">
        <div class="media-left">
            <img src="{{ $image }}" class="media-object">
        </div>
        <div class="media-body">
            <div class="skew">
                <div class="unskew">
                    <span class="text-default title-text">{{ $title }}</span>
                </div>
                <div class="media-bar">
                    <div class="progress">
                        <div class="progress-bar progress-bar-{{ $color ?? 'default' }}"
                             role="progressbar"
                             aria-valuemin="{{ $percentComplete }}"
                             aria-valuemax="100"
                             style="width: {{ $percentComplete }}%;">
                            <p class="unskew">{{ $progressMessage }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>