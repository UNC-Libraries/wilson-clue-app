@if(session('alert'))
    <div class="alert alert-{{session('alert.type')}}" id="flash-message">
        <p>
            @if(session('alert.type') == 'warning')
                <span class="fa fa-exclamation-circle"></span>
            @endif
            @if(session('alert.type') == 'danger')
                <span class="fa fa-exclamation-triangle"></span>
            @endif
            @if(session('alert.type') == 'success')
                <span class="fa fa-check-circle"></span>
            @endif
            @if(session('alert.type') == 'info')
                <span class="fa fa-info-circle"></span>
            @endif
            {{ session('alert.message') }}
        </p>
    </div>
@endif