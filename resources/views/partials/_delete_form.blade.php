{{ $route }}
{{ html()->form('DELETE', route($route))->class('confirm-submit float-end')->data('message', empty($message) ? 'Are you sure you want to delete this?' : $message)->open() }}
    <button type="submit" class="btn btn-danger {{ $class ?? 'float-end' }}"><span class="fa fa-trash"></span></button>
{{ html()->form()->close() }}