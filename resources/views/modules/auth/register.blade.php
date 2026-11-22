@extends('layouts.auth') @section('title', 'Crear cuenta · Aetherix') @section('content')<div class="auth-card"><span
        class="eyebrow">Únete a Aetherix</span>
    <h1>Crea tu cuenta</h1>
    <p class="sub">Tu próxima historia te espera.</p>
    <form action="{{ route('register') }}" method="GET">
        <div class="field"><input name="name" id="name" placeholder=" " required><label for="name">Nombre
                completo</label></div>
        <div class="field"><input type="email" name="email" id="email" placeholder=" " required><label
                for="email">Correo electrónico</label></div>
        <div class="field"><input type="password" name="password" id="password" placeholder=" " required><label
                for="password">Contraseña</label></div><button class="btn btn-primary btn-lg btn-block">Crear
            cuenta</button>

        <button type="button" class="btn btn-google btn-lg btn-block">Continuar con Google</button>
    </form>
    <p class="auth-alt">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</div>@endsection
