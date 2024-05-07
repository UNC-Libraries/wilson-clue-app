<div class="col-12 special-notice">
    <h2 class="text-end"><span class="special-text">Special</span><span class="notice-text">Notice</span></h2>
    <div class="row">
        <div class="col-12 col-sm-8 special-notice-text">
            {!! app(Parsedown::class)->text($special_notice) !!}
        </div>
        <div class="col-12 col-sm-4 text-center" style="margin-bottom: 2em;">
            <h3>{{date('F j',strtotime($game->start_time))}} @ {{date('g:i A',strtotime($game->start_time))}}</h3>
            <a href="{{ route('enlist.index') }}" class="btn btn-lg btn-danger">@choice('enlist.enlist',$game->spots_left)</a>
            <p>@choice('enlist.left',$game->spots_left,['spot'=>$game->spots_left])</p>

            <p style="margin-top: 75px; margin-bottom: 5px;">Already Signed Up?</p>
            <a href="{{ route('enlist.teamManagement') }}" class="btn btn-danger">Manage your team</a>
        </div>
    </div>
</div>