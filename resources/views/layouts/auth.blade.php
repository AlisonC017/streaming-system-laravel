<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aetherix')</title>
    @include('layouts.css')
</head>

<body class="auth-body">

    <div class="auth-page">

        <div class="auth-top">
            <a class="brand" href="{{ url('/') }}">Aetherix</a>
        </div>

        <main class="auth-main">
            @include('partials.alertas')
            @yield('content')
        </main>

        <p class="auth-foot">
            &copy; {{ date('Y') }} Aetherix
        </p>
    </div>

    <script src="{{ asset('theme/js/app.js') }}"></script>
</body>

</html>
