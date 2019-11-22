@php($Parsedown = new Parsedown())
<div class="col-xs-12 text-center">
    <div class="site-alert">{!! $Parsedown->text($homepageAlert) !!}</div>
</div>