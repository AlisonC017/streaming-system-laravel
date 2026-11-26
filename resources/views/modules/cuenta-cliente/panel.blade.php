@extends('layouts.cuenta')

@section('titulo', 'Mi panel · Aetherix')
@section('titulo_pagina', 'Mi panel')

@php
    $suscripcion = $suscripcion ?? null;
    $planes = $planes ?? [];
    $vigente = $suscripcion && $suscripcion->estado !== 'Cancelada';
    $fecha = fn($f) => $f ? \Carbon\Carbon::parse($f)->format('d/m/Y') : '—';
@endphp

@section('contenido')
    @include('partials.alertas')

    @if (!$vigente)
        <div class="banner">
            <div>
                <h2>Aún no tienes una suscripción</h2>
                <p>Elige un plan para empezar a ver todo el catálogo.</p>
            </div>
            @include('partials.badge-estado', ['estado' => $suscripcion->estado ?? null])
        </div>
    @else
        <div class="banner {{ in_array($suscripcion->estado, ['Suspendida', 'Pendiente']) ? 'banner--alerta' : '' }}">
            <div>
                <h2>Estás suscrito a {{ $suscripcion->nombre_plan }}</h2>
                <p>
                    Estado: {{ $suscripcion->estado }} ·
                    Próximo cobro: {{ $fecha($suscripcion->fecha_proximo_cobro) }}
                </p>
            </div>
            @include('partials.badge-estado', ['estado' => $suscripcion->estado])
        </div>
    @endif

    <div class="planes-grid" style="margin-top: 1.5rem;">
        @foreach ($planes as $plan)
            @include('partials.plan-card', ['plan' => $plan, 'suscripcion' => $suscripcion])
        @endforeach
    </div>
@endsection
