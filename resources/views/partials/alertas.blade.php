@if (session('exito'))
    <div class="alerta alerta-exito">{{ session('exito') }}</div>
@endif

@if (session('error'))
    <div class="alerta alerta-error">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alerta alerta-error">
        <ul>
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
