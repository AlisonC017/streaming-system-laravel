@extends('layouts.auth')
@section('title', 'Iniciar sesión · Aetherix')
@section('content')
    <div class="auth-card">
        <span class="eyebrow">Qué bueno tenerte de vuelta</span>
        <h1>Iniciar sesión</h1>
        <p class="sub">Continúa donde dejaste tu historia.</p>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="field">
                <input type="email" name="correo" id="correo" placeholder=" " value="{{ old('correo') }}" required>
                <label for="correo">Correo electrónico</label>
            </div>
            <div class="field">
                <input type="password" name="password" id="password" placeholder=" " required>
                <label for="password">Contraseña</label>
                <button type="button" class="toggle" data-toggle-pass="password">◉</button>
            </div>

            <button class="btn btn-primary btn-lg btn-block">Ingresar</button>

            <button type="button" class="btn btn-google btn-lg btn-block">Continuar con Google</button>
        </form>

        <p class="auth-alt">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
    </div>

    <script>
        document.querySelectorAll('[data-toggle-pass]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var input = document.getElementById(btn.dataset.togglePass);
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    </script>
@endsection

