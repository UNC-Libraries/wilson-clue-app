<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    @yield('css')

    <!-- META -->
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="Description" content="Live-action Clue at UNC Chapel Hill. Hosted at Wilson Library" />
    <meta name="robots" content="index, follow" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
</head>

{{-- $controller and $action are injected in the AppServiceProvider
and are used to route JavaScript in router.js --}}
<body data-controller="{{$controller}}" data-action="{{$action}}">

    @yield('main.content')

    <script src="{{asset('js/app.js')}}"></script>
    @yield('additional_scripts')
</body>

</html>