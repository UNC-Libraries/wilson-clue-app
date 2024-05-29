{{ html()->form('DELETE', route($route))->class('confirm-submit')->data('message', empty($message) ? 'Are you sure you want to delete this?' : $message)->open() }}
    <button type="submit" class="btn btn-danger {{ $class ?? 'pull-right' }}"><span class="fa fa-trash"></span></button>
{{ html()->form()->close() }}