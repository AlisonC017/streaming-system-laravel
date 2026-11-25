{{-- Confirmar suscripcion / cambio de plan
     Variables: $plan (plan elegido), $metodos (tarjetas activas del cliente), $suscripcion (opcional)
     Backend al enviar: sp_insert_suscripcion o sp_update_suscripcion,
                        sp_registrar_tarjeta_encriptada (si es tarjeta nueva) y sp_procesar_cobro_suscripcion --}}
@extends('layouts.cuenta')

@section('titulo', 'Confirmar plan · Aetherix')
@section('titulo_pagina', 'Confirmar suscripción')

@php
    $metodos = $metodos ?? [];
    $suscripcion = $suscripcion ?? null;
    $esCambio = $suscripcion && $suscripcion->estado !== 'Cancelada';
    $accion = Route::has('suscripcion.confirmar') ? route('suscripcion.confirmar') : url('/suscripcion/confirmar');
    $urlPlanes = Route::has('planes') ? route('planes') : url('/planes');
@endphp

@section('contenido')
    <div class="banner">
        <div>
            <h2>{{ $plan->nombre_plan }} · {{ $plan->calidad }}</h2>
            <p>${{ number_format($plan->precio, 2) }} al mes
                @if ($esCambio)
                    · reemplaza tu plan actual ({{ $suscripcion->nombre_plan ?? 'actual' }})
                @endif
            </p>
        </div>
        <a class="btn btn-ghost btn-sm" href="{{ $urlPlanes }}">Elegir otro plan</a>
    </div>

    <form method="POST" action="{{ $accion }}" style="display:grid;gap:1.25rem">
        @csrf
        <input type="hidden" name="id_plan" value="{{ $plan->id_plan }}">

        <h2 class="cuenta-sec-title" style="margin:1rem 0 0">Método de pago</h2>

        @if (count($metodos))
            <div class="tabla-wrap">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th style="width:3rem"></th>
                            <th>Tarjeta</th>
                            <th>Vence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metodos as $i => $m)
                            <tr>
                                <td>
                                    <input type="radio" name="id_metodo" id="metodo{{ $m->id_metodo }}"
                                        value="{{ $m->id_metodo }}" @checked(old('id_metodo', $i === 0 ? $m->id_metodo : null) == $m->id_metodo)>
                                </td>
                                <td class="mono"><label for="metodo{{ $m->id_metodo }}">**** {{ $m->ultimos4 }}</label></td>
                                <td class="mono">{{ \Carbon\Carbon::parse($m->fecha_vencimiento)->format('m/Y') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><input type="radio" name="id_metodo" id="metodoNuevo" value="nueva" @checked(old('id_metodo') === 'nueva')></td>
                            <td colspan="2"><label for="metodoNuevo">Usar una tarjeta nueva</label></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <input type="hidden" name="id_metodo" value="nueva">
        @endif

        <div id="tarjetaNueva" class="cuenta-card" @if (count($metodos) && old('id_metodo') !== 'nueva') hidden @endif>
            <small>Tarjeta nueva</small>
            <p>Se guarda encriptada; en tu cuenta solo verás los últimos 4 dígitos.</p>
            <div class="field">
                <input name="numero_tarjeta" id="numero_tarjeta" inputmode="numeric" autocomplete="cc-number"
                    maxlength="19" pattern="[0-9 ]{13,19}" placeholder=" ">
                <label for="numero_tarjeta">Número de tarjeta</label>
            </div>
            <div class="field">
                <input type="month" name="fecha_vencimiento" id="fecha_vencimiento" placeholder=" "
                    min="{{ now()->format('Y-m') }}" value="{{ old('fecha_vencimiento') }}">
                <label for="fecha_vencimiento">Vencimiento</label>
            </div>
        </div>

        <div class="banner">
            <div>
                <h2>Total hoy: ${{ number_format($plan->precio, 2) }}</h2>
                <p>Después se cobra cada mes en la misma fecha.</p>
            </div>
            <button type="submit" class="btn btn-primary">
                {{ $esCambio ? 'Confirmar cambio de plan' : 'Pagar y suscribirme' }}
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // muestra el formulario de tarjeta nueva solo cuando se elige esa opcion
        document.querySelectorAll('input[name="id_metodo"]').forEach(function (r) {
            r.addEventListener('change', function () {
                document.getElementById('tarjetaNueva').hidden = this.value !== 'nueva';
            });
        });
    </script>
@endpush
