<div class="row">
    @foreach($models as $mini)
        <div class="col-6 col-xs-4 col-sm-3">
            <div class="card card-body text-center">
                <p class="lead">{{ $mini->name }}</p>
                <div class="text-center">
                    {!! Html::image($mini->src,null,array('class'=>'media-object-128')) !!}
                </div>
                <p>{{ $mini->year }}</p>
                <a href="{{ route('admin.minigameImage.edit', $mini->id) }}" class="btn btn-primary btn-sm">
                    <span class="fa fa-edit"></span> Edit
                </a>
            </div>
        </div>
    @endforeach
</div>
