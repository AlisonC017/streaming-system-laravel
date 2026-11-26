{{-- Mi suscripcion
     Variables: $suscripcion (null si no tiene): id_suscripcion, id_plan, nombre_plan, precio, calidad,
                fecha_inicio, fecha_proximo_cobro, estado, fecha_cancelacion
                $planes (para cambiar de plan)
     Backend: sp_obtener_suscripcion_por_id, sp_listar_planes, cancelar -> sp_update_suscripcion (estado Cancelada) --}}
@extends('layouts.cuenta')

@section('titulo', 'Mi suscripción · Aetherix')
@section('titulo_pagina', 'Mi suscripción')

@php
    $suscripcion = $suscripcion ?? null;
    $planes = $planes ?? [];
    $vigente = $suscripcion && $suscripcion->estado !== 'Cancelada';
    $fecha = fn($f) => $f ? \Carbon\Carbon::parse($f)->format('d/m/Y') : '—';
    $urlCancelar = Route::has('suscripcion.cancelar') ? route('suscripcion.cancelar') : url('/suscripcion/cancelar');
    $urlMetodos = Route::has('pagos.metodos') ? route('pagos.metodos') : url('/pagos/metodos');
    $urlPagar =
        $suscripcion && Route::has('suscripcion.checkout')
            ? route('suscripcion.checkout', $suscripcion->id_plan)
            : null;
@endphp

@section('contenido')
    @if (!$suscripcion)
        <div class="banner">
            <div>
                <h2>No tienes una suscripción</h2>
                <p>Elige un plan para ver todo el catálogo.</p>
            </div>
            @include('partials.badge-estado', ['estado' => null])
        </div>
    @else
        <div class="banner {{ in_array($suscripcion->estado, ['Suspendida', 'Pendiente']) ? 'banner--alerta' : '' }}">
            <div>
                <h2>{{ $suscripcion->nombre_plan }} · {{ $suscripcion->calidad ?? '' }}</h2>
                <p>
                    @switch($suscripcion->estado)
                        @case('Activa')
                            Tu próximo cobro de ₡{{ number_format($suscripcion->precio, 2) }} es el
                            {{ $fecha($suscripcion->fecha_proximo_cobro) }}.
                        @break

                        @case('Pendiente')
                            Tu suscripción está pendiente del primer pago.
                        @break

                        @case('Suspendida')
                            No pudimos cobrar tu tarjeta. Actualiza tu método de pago para seguir viendo.
                        @break

                        @case('Cancelada')
                            Cancelaste tu suscripción el {{ $fecha($suscripcion->fecha_cancelacion) }}.
                        @break
                    @endswitch
                </p>
            </div>
            @include('partials.badge-estado', ['estado' => $suscripcion->estado])
        </div>

        @if (in_array($suscripcion->estado, ['Suspendida', 'Pendiente']) && $urlPagar)
            <div>
                <a class="btn btn-primary" href="{{ $urlPagar }}">Pagar ahora</a>
                <a class="btn btn-ghost" href="{{ $urlMetodos }}">Administrar tarjetas</a>
            </div>
        @endif

        <div class="atajos">
            <div class="cuenta-card">
                <small>Precio</small>
                <strong>₡{{ number_format($suscripcion->precio, 2) }} / mes</strong>
            </div>
            <div class="cuenta-card">
                <small>Miembro desde</small>
                <strong>{{ $fecha($suscripcion->fecha_inicio) }}</strong>
            </div>
            <div class="cuenta-card">
                <small>Próximo cobro</small>
                <strong>{{ $vigente ? $fecha($suscripcion->fecha_proximo_cobro) : '—' }}</strong>
            </div>
            <div class="cuenta-card">
                <small>Método de pago</small>
                <strong>{{ !empty($suscripcion->ultimos4) ? '**** ' . $suscripcion->ultimos4 : 'Sin tarjeta' }}</strong>
                <a href="{{ $urlMetodos }}">Administrar tarjetas</a>
            </div>
        </div>
    @endif

    @if (count($planes))
        <h2 class="cuenta-sec-title" style="margin-top:2rem">{{ $vigente ? 'Cambiar de plan' : 'Planes disponibles' }}</h2>
        <div class="planes-grid">
            @foreach ($planes as $plan)
                @include('partials.plan-card', ['plan' => $plan, 'suscripcion' => $suscripcion])
            @endforeach
        </div>
    @endif

    @if ($vigente)
        <div class="banner banner--alerta" style="margin-top:2rem">
            <div>
                <h2>Cancelar suscripción</h2>
                <p>Dejarás de tener acceso al catálogo y no se harán más cobros.</p>
            </div>
            <form method="POST" action="{{ $urlCancelar }}"
                onsubmit="return confirm('¿Seguro que quieres cancelar tu suscripción?')">
                @csrf
                <input type="hidden" name="id_suscripcion" value="{{ $suscripcion->id_suscripcion }}">
                <button type="submit" class="btn btn-ghost btn-sm">Cancelar suscripción</button>
            </form>
        </div>
    @endif
@endsection
