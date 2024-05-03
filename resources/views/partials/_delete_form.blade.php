{!! Form::open([
    'route' => $route,
    'method'=>'DELETE',
    'class' => 'confirm-submit float-end',
    'data-message' => empty($message) ? 'Are you sure you want to delete this?' : $message]) !!}
    <button type="submit" class="btn btn-danger {{ $class ?? 'float-end' }}"><span class="fa fa-trash"></span></button>
{!! Form::close() !!}