@extends('layouts.auth')
@section('title', 'Crear cuenta · Aetherix')
@section('content')
    <div class="auth-card">
        <span class="eyebrow">Únete a Aetherix</span>
        <h1>Crea tu cuenta</h1>
        <p class="sub">Tu próxima historia te espera.</p>

        <form action="{{ route('register.post') }}" method="POST">
            @csrf

            <div class="field">
                <input name="nombre" id="nombre" placeholder=" " value="{{ old('nombre') }}" required>
                <label for="nombre">Nombre</label>
            </div>
            <div class="field">
                <input name="apellidos" id="apellidos" placeholder=" " value="{{ old('apellidos') }}" required>
                <label for="apellidos">Apellidos</label>
            </div>
            <div class="field">
                <input type="email" name="correo" id="correo" placeholder=" " value="{{ old('correo') }}" required>
                <label for="correo">Correo electrónico</label>
            </div>
            <div class="field">
                <input type="password" name="password" id="password" placeholder=" " required>
                <label for="password">Contraseña (mínimo 8 caracteres)</label>
            </div>
            <div class="field">
                <input name="pais" id="pais" placeholder=" " value="{{ old('pais') }}" required>
                <label for="pais">País</label>
            </div>
            <div class="field">
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" placeholder=" "
                    value="{{ old('fecha_nacimiento') }}" required>
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
            </div>

            <button class="btn btn-primary btn-lg btn-block">Crear cuenta</button>

            <button type="button" class="btn btn-google btn-lg btn-block">Continuar con Google</button>

        </form>

        <p class="auth-alt">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
    </div>
@endsection
