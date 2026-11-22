{{-- cliente y el panel de admin, cambia el menu lateral segun session('nivel') 
sin nivel -> cliente   |   Total, Soporte o Contenido -> administrador --}}

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('titulo', 'Aetherix')</title>
    @include('layouts.css')
    <link rel="stylesheet" href="{{ asset('admin-theme/css/cuenta.css') }}">
</head>

<body>
    @include('partials.header')

    <main class="cuenta">
        <div class="wrap cuenta-grid">
            <aside class="cuenta-side" aria-label="Menú">
                @include('partials.menu-panel')
            </aside>

            <section class="cuenta-main">
                @include('partials.alertas')

                @hasSection('titulo_pagina')
                    <header class="cuenta-head">
                        <h1>@yield('titulo_pagina')</h1>
                    </header>
                @endif

                @yield('contenido')
            </section>
        </div>
    </main>

    @include('partials.footer')

    <script src="{{ asset('theme/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme/js/main.js') }}"></script>
    @stack('scripts')
    @yield('scripts')
</body>

</html>
