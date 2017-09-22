@foreach($models as $agent)
    <div class="col-sm-12 col-md-6">
        <div class="well well-sm">
            <div class="row">
                <div class="hidden-xs col-sm-4 col-lg-3">
                    {!! Html::image($agent->src,$agent->full_name, ['class' => 'img-responsive']) !!}
                </div>
                <div class="col-xs-12 col-sm-8 col-lg-9">
                    <div class="row">
                        <div class="col-xs-9">
                            <h4 class="media-heading"><small>{{ $agent->title }}</small><br>{{ $agent->full_name }} </h4>
                            @if($agent->admin)
                                <span class="label label-info">Admin</span>
                            @endif
                            @if($agent->web_display)
                                <span class="label label-success">Web Display</span>
                            @endif
                            @if($agent->retired)
                                <span class="label label-danger">Retired</span>
                            @endif
                        </div>
                        <div class="col-xs-3 text-right">
                            <a href="{{ route('admin.agent.edit',['id'=>$agent->id]) }}" class="btn btn-primary btn-sm">
                                Edit
                            </a>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 1em;">
                        <div class="col-xs-12">
                            <dl>
                                <dt><strong>Job Title</strong></dt>
                                <dd>{{ $agent->job_title }}</dd>
                                <dt><strong>Location</strong></dt>
                                <dd>{{ $agent->location }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach