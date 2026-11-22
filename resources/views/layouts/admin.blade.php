<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76"
        href="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png"
        href="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/img/favicon.png') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>@yield('titulo', 'Panel Admin - Aetherix')</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
        name='viewport' />

    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
    <!-- Nucleo Icons -->
    <link href="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <!-- CSS Files -->
    <link href="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/css/black-dashboard.css') }}"
        rel="stylesheet" />
</head>

<body class="">
    <div class="wrapper">

        <!-- MENÚ LATERAL -->
        @include('partials.menu-admin')

        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-toggle d-inline">
                            <button type="button" class="navbar-toggler">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </button>
                        </div>
                        <a class="navbar-brand" href="#">@yield('titulo_pagina', 'Dashboard')</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation"
                        aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navigation">
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item d-flex align-items-center mr-3">
                                <span class="badge badge-primary">Nivel: {{ session('nivel', 'Admin') }}</span>
                            </li>
                            <li class="dropdown nav-item">
                                <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                    <div class="photo">
                                        <img src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/img/anime3.png') }}"
                                            alt="Perfil">
                                    </div>
                                    <b class="caret d-none d-lg-block d-xl-block"></b>
                                    <p class="d-lg-none">Opciones</p>
                                </a>
                                <ul class="dropdown-menu dropdown-navbar">
                                    <form method="POST" action="{{ url('/logout') }}">@csrf
                                        <button class="nav-item dropdown-item" type="submit">Cerrar sesión</button>
                                    </form>
                                </ul>
                            </li>
                            <li class="separator d-lg-none"></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- End Navbar -->

            <!-- CONTENIDO DINÁMICO DE CADA VISTA -->
            <div class="content">
                @yield('contenido')
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="copyright float-right">
                        ©
                        <script>
                            document.write(new Date().getFullYear())
                        </script> Aetherix — Panel Administrativo
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/core/jquery.min.js') }}"></script>
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/plugins/perfect-scrollbar.jquery.min.js') }}">
    </script>
    <!-- Chart JS -->
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/plugins/chartjs.min.js') }}"></script>
    <!-- Notifications Plugin -->
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/plugins/bootstrap-notify.js') }}"></script>
    <!-- Control Center for Black Dashboard -->
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/black-dashboard.min.js') }}"
        type="text/javascript"></script>

    @yield('scripts')
</body>

</html>
