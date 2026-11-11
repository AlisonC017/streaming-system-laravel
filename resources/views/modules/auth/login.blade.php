@extends('layouts.auth') @section('title', 'Iniciar sesión · Stream Line') @section('content')<div class="auth-card"><span
        class="eyebrow">Qué bueno tenerte de vuelta</span>
    <h1>Iniciar sesión</h1>
    <p class="sub">Continúa donde dejaste tu historia.</p>
    <form action="{{ route('login') }}" method="GET">
        <div class="field"><input type="email" name="email" id="email" placeholder=" " required><label
                for="email">Correo electrónico</label></div>
        <div class="field"><input type="password" name="password" id="password" placeholder=" " required><label
                for="password">Contraseña</label><button type="button" class="toggle"
                data-toggle-pass="password">◉</button></div><button
            class="btn btn-primary btn-lg btn-block">Ingresar</button>
    </form>
    <p class="auth-alt">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
    <p class="auth-note">Demostración visual; aún no valida usuarios.</p>
</div>@endsection
