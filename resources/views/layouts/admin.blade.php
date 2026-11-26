{{-- Layout de administración (tema Black Dashboard). Independiente de layouts/panel.
     Solo entran los tres niveles de admin. --}}
@php abort_unless(in_array(session('nivel'), ['Total', 'Soporte', 'Contenido'], true), 403); @endphp
<!DOCTYPE html>
<html lang="es">

<style>
    /* Foto de perfil */
    .navbar .photo {
        width: 46px !important;
        height: 46px !important;
        border-radius: 50% !important;
        overflow: hidden;
    }

    .navbar .photo img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
    }

    .navbar .badge {
        font-size: 12px !important;
        padding: 7px 12px !important;
    }

    /* Alinear todo verticalmente */
    .navbar .navbar-nav {
        align-items: center;
    }

    .navbar .dropdown-toggle {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
</style>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <title>@yield('titulo', 'Administració - Aetherix')</title>

    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
    <link href="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/css/black-dashboard.css') }}"
        rel="stylesheet" />
</head>

<body class="">
    <div class="wrapper">
        @include('partials.menu-admin')

        <div class="main-panel">

            {{-- Navbar --}}
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
                        <a class="navbar-brand" href="{{ url('/admin') }}">@yield('titulo_pagina', 'Dashboard')</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation"
                        aria-controls="navigation" aria-expanded="false" aria-label="Menú">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navigation">
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item d-flex align-items-center mr-3">
                                <span class="badge badge-primary">Nivel: {{ session('nivel') }}</span>
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
                                    <li class="nav-link">
                                        <form method="POST" action="{{ url('/logout') }}">
                                            @csrf
                                            <button type="submit" class="nav-item dropdown-item">Cerrar sesión</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            <li class="separator d-lg-none"></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="content">
                @include('partials.alertas')
                @yield('contenido')
            </div>
        </div>
    </div>

    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/core/jquery.min.js') }}"></script>
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/plugins/perfect-scrollbar.jquery.min.js') }}">
    </script>
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/plugins/chartjs.min.js') }}"></script>
    <script src="{{ asset('admin-theme/black-dashboard-html-v1.0.0/assets/js/black-dashboard.min.js') }}"></script>
    @yield('scripts')
</body>

</html>
