@extends('layouts.auth')
@section('title', 'Completa tus datos · Aetherix')
@section('content')
    <div class="auth-card">
        <span class="eyebrow">Ya casi estás</span>
        <h1>Completa tus datos</h1>
        <p class="sub">Entraste con Google como {{ $pendiente['correo'] }}. Solo faltan dos datos para crear tu cuenta.</p>

        <form action="{{ route('google.completar.post') }}" method="POST">
            @csrf

            <div class="field">
                <input name="nombre" id="nombre" placeholder=" " value="{{ old('nombre', $pendiente['nombre']) }}" required>
                <label for="nombre">Nombre</label>
            </div>
            <div class="field">
                <input name="apellidos" id="apellidos" placeholder=" "
                    value="{{ old('apellidos', $pendiente['apellidos']) }}" required>
                <label for="apellidos">Apellidos</label>
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

            <button class="btn btn-primary btn-lg btn-block">Crear cuenta y continuar</button>
        </form>

        <p class="auth-alt"><a href="{{ route('login') }}">Cancelar</a></p>
    </div>
@endsection