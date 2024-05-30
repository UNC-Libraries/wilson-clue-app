{{-- See vendor/laravel/framework/src/Illuminate/Foundation/helpers.php for how route() works.
The first option is a string with the route. The second option is the parameters as an array.
Parameters come in as a single array so shifting off the first element, which is the route path --}}
{{ html()->form('DELETE', route(array_shift($route), $route))->class('confirm-submit float-end')->data('message', empty($message) ? 'Are you sure you want to delete this?' : $message)->open() }}
    <button type="submit" class="btn btn-danger {{ $class ?? 'float-end' }}"><span class="fa fa-trash"></span></button>
{{ html()->form()->close() }}