<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('titulo', 'Aetherix')</title>
    @include('layouts.css')

    <style>
        .alertas-flotantes {
            position: fixed;
            top: calc(var(--header-h, 72px) + 12px);
            left: 50%;
            transform: translateX(-50%);
            width: min(92%, 560px);
            z-index: 1100;
            animation: alertaDesaparece 0.6s ease 5s forwards;
        }

        .alertas-flotantes .alerta {
            background: rgba(20, 10, 15, 0.95);
            color: #ffd7d7;
        }

        @keyframes alertaDesaparece {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
    </style>
</head>

<body>
    @include('partials.header')

    <main>
        <main>
            <div class="alertas-flotantes">
                @include('partials.alertas')
            </div>
            @yield('contenido')
        </main>
        @yield('contenido')
    </main>

    @include('partials.footer')

    <script src="{{ asset('theme/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme/js/main.js') }}"></script>
    @stack('scripts')
</body>

</html>
