{{--  menu para cliente y administradores / Para dar o quitar acceso a una opción,
     se edita solo la lista de roles de esa fila
     Roles: cliente | Total | Soporte | Contenido
     Nombres de ruta que deben existir en el backend (si no existen, se usa la ruta de prueba):
       cuenta.panel · suscripcion.mia · pagos.metodos · cuenta.historial --}}
@php
    $nivel = session('nivel'); // null = cliente
    $rol = $nivel ?: 'cliente';
    $admins = ['Total', 'Soporte', 'Contenido'];
    $enlace = fn($nombre, $respaldo) => Route::has($nombre) ? route($nombre) : url($respaldo);

    // [texto, url, patrones para marcar la opción activa, roles que la ven]
    $items = [
        // --- Cliente ---
        ['Mi panel', $enlace('cuenta.panel', '/demo/panel'), ['cuenta', 'demo/panel'], ['cliente']],
        ['Mi suscripción', $enlace('suscripcion.mia', '/suscripcion'), ['suscripcion', 'suscripcion/mi*'], ['cliente']],
        ['Métodos de pago', $enlace('pagos.metodos', '/pagos/metodos'), ['pagos*'], ['cliente']],
        [
            'Historial de pagos',
            $enlace('cuenta.historial', '/demo/historial'),
            ['cuenta/historial', 'demo/historial'],
            ['cliente'],
        ],

        // --- Administración ---
        ['Resumen', url('/admin'), ['admin'], $admins],
        ['Reportes', url('/admin/reportes'), ['admin/reportes*'], $admins],
        ['Auditoría', url('/admin/auditoria'), ['admin/auditoria*', 'demo/auditoria'], $admins],
        ['Planes', url('/admin/planes'), ['admin/planes*'], $admins],
        ['Clientes', url('/admin/usuarios'), ['admin/usuarios*'], ['Total', 'Soporte']],
        ['Cobros', url('/admin/cobros'), ['admin/cobros*'], ['Total']],
        ['Administradores', url('/admin/administradores'), ['admin/administradores*'], ['Total']],
    ];

    $visibles = collect($items)->filter(fn($i) => in_array($rol, $i[3], true));
@endphp

<h2>{{ $nivel ? 'Administración' : 'Mi cuenta' }}</h2>

@if ($nivel)
    <p class="cuenta-nivel"><span class="chip chip--gold">Nivel: {{ $nivel }}</span></p>
@endif

<ul class="cuenta-nav">
    @foreach ($visibles as [$texto, $url, $patrones, $roles])
        <li>
            <a href="{{ $url }}"
                @if (request()->is(...$patrones)) aria-current="page" @endif>{{ $texto }}</a>
        </li>
    @endforeach

    <li class="cuenta-nav__sep">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>
    </li>
</ul>
