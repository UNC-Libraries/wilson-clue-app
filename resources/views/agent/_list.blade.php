<div class="row">
    @foreach($models as $agent)
        <div class="col-sm-12 col-md-6">
            <div class="card card-body well-sm">
                <div class="row">
                    <div class="d-none col-xs-4 col-md-3">
                        {!! Html::image($agent->src,$agent->full_name, ['class' => 'img-fluid']) !!}
                    </div>
                    <div class="col-12 col-xs-8 col-md-9">
                        <div class="row">
                            <div class="col-9">
                                <h4 class="media-heading"><small>{{ $agent->title }}</small><br>{{ $agent->full_name }} </h4>
                                @if($agent->admin)
                                    <span class="badge bg-info">Admin</span>
                                @endif
                                @if($agent->web_display)
                                    <span class="badge bg-success">Web Display</span>
                                @endif
                                @if($agent->retired)
                                    <span class="badge bg-danger">Retired</span>
                                @endif
                            </div>
                            <div class="col-3 text-end">
                                <a href="{{ route('admin.agent.edit',[$agent->id]) }}" class="btn btn-primary btn-sm">
                                    Edit
                                </a>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 1em;">
                            <div class="col-12">
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
</div>