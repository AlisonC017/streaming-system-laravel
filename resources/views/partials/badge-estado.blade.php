{{-- Etiqueta de estado de una suscripcion o tarjeta
     Uso: @include('partials.badge-estado', ['estado' => $suscripcion->estado ?? null])
     Estados: Activa | Pendiente | Suspendida | Cancelada | Activo | Inactivo | null (sin suscripcion) --}}
@php
    $estado = $estado ?? null;
    $clase = match ($estado) {
        'Activa', 'Activo' => 'activa',
        'Pendiente' => 'pendiente',
        'Suspendida' => 'suspendida',
        'Cancelada', 'Inactivo' => 'cancelada',
        default => 'sin',
    };
@endphp
<span class="badge-estado badge-estado--{{ $clase }}">{{ $estado ?? 'Sin suscripción' }}</span>
