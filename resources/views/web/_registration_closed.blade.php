<div class="col-xs-12 text-center" style="margin-bottom: 5em;">
    <div class="site-alert">{!! app(Parsedown::class)->text($registration_closed) !!}</div>
    <p>
        <a href="{{ route('enlist.teamManagement') }}" class="btn btn-danger">Manage your team</a>
    </p>
</div>