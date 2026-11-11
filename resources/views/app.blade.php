<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('titulo', 'Stream Line')</title>@include('layouts.css')
</head>

<body>
    @includeIf('partials.icons')
    @include('layouts.header')

    <main>
        @yield('contenido')
    </main>

    @include('layouts.footer')

    <script src="{{ asset('theme/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme/js/main.js') }}"></script>
    @stack('scripts')
</body>

</html>
