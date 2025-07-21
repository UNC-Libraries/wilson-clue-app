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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/jQuery-provider.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.js" crossorigin="anonymous"></script>
</head>

{{-- $controller and $action are injected in the AppServiceProvider
and are used to route JavaScript in router.js --}}
<body data-controller="{{$controller}}" data-action="{{$action}}">

    @yield('main.content')

    @yield('additional_scripts')
    @vite('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js?commonjs-entry')
    @vite(['resources/assets/js/app.js', 'resources/assets/js/router.js'])
</body>

</html>