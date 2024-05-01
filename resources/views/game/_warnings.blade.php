@if(!empty($warnings))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <p class="text-center">
                    <i class="fa fa-warning"></i>
                    This game has some issues
                    <i class="fa fa-warning"></i>
                </p>
                <ul>
                    @foreach($warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif