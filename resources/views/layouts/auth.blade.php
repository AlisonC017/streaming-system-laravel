
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Stream Line')</title>
    @include('layouts.css')
</head>
<body class="auth-body">

    <div class="auth-page">

        <div class="auth-top">
            <a class="brand" href="{{ url('/') }}">Stream Line</a>
        </div>

        <main class="auth-main">
            @yield('content')
        </main>

        <p class="auth-foot">
            &copy; {{ date('Y') }} Stream Line
        </p>
    </div>

    <script src="{{ asset('theme/js/app.js') }}"></script>
</body>
</html>
