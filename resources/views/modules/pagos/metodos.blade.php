{{-- Metodos de pago del cliente
     Variables: $metodos: id_metodo, ultimos4, fecha_vencimiento, estado
     Backend: listar -> SELECT id_metodo, ultimos4, fecha_vencimiento, estado (nunca numero_tarjeta)
              agregar -> sp_registrar_tarjeta_encriptada
              eliminar -> sp_delete_metodo_pago (el trigger la inactiva si tiene pagos) --}}
@extends('layouts.cuenta')

@section('titulo', 'Métodos de pago · Aetherix')
@section('titulo_pagina', 'Métodos de pago')

@php
    $metodos = $metodos ?? [];
    $urlGuardar = Route::has('pagos.metodos.guardar') ? route('pagos.metodos.guardar') : url('/pagos/metodos');
    $urlEliminar = fn($id) => Route::has('pagos.metodos.eliminar')
        ? route('pagos.metodos.eliminar', $id)
        : url('/pagos/metodos/' . $id);
@endphp

@section('contenido')
    <h2 class="cuenta-sec-title" style="margin-top:1rem">Mis tarjetas</h2>

    @if (count($metodos))
        <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Tarjeta</th>
                        <th>Vence</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($metodos as $m)
                        @php $vencida = \Carbon\Carbon::parse($m->fecha_vencimiento)->endOfMonth()->isPast(); @endphp
                        <tr>
                            <td class="mono">**** {{ $m->ultimos4 }}</td>
                            <td class="mono">
                                {{ \Carbon\Carbon::parse($m->fecha_vencimiento)->format('m/Y') }}
                                @if ($vencida) <span class="chip">Vencida</span> @endif
                            </td>
                            <td>@include('partials.badge-estado', ['estado' => $m->estado])</td>
                            <td>
                                @if ($m->estado === 'Activo')
                                    <form method="POST" action="{{ $urlEliminar($m->id_metodo) }}"
                                        onsubmit="return confirm('¿Eliminar la tarjeta **** {{ $m->ultimos4 }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="vacio">Aún no tienes tarjetas registradas.</p>
    @endif

    <h2 class="cuenta-sec-title" style="margin-top:2rem">Agregar tarjeta</h2>
    <form method="POST" action="{{ $urlGuardar }}" class="cuenta-card">
        @csrf
        <p>Tu número se guarda encriptado (AES-256). Solo mostramos los últimos 4 dígitos.</p>
        <div class="field">
            <input name="numero_tarjeta" id="numero_tarjeta" inputmode="numeric" autocomplete="cc-number"
                maxlength="19" pattern="[0-9 ]{13,19}" placeholder=" " required>
            <label for="numero_tarjeta">Número de tarjeta</label>
        </div>
        <div class="field">
            <input type="month" name="fecha_vencimiento" id="fecha_vencimiento" placeholder=" "
                min="{{ now()->format('Y-m') }}" value="{{ old('fecha_vencimiento') }}" required>
            <label for="fecha_vencimiento">Vencimiento</label>
        </div>
        <button type="submit" class="btn btn-primary">Guardar tarjeta</button>
    </form>
@endsection
