{{-- Pagina publica de planes
     Variables: $planes (lista de planes), $suscripcion (opcional, si el cliente ya inicio sesion)
     Backend: EXEC sp_listar_planes --}}
@extends('layouts.app')

@section('titulo', 'Planes · Aetherix')

@section('contenido')
    <link rel="stylesheet" href="{{ asset('admin-theme/css/cuenta.css') }}">

    <section class="cuenta">
        <div class="wrap">
            <header class="cuenta-head" style="margin-bottom:1.5rem">
                <h1>Elige tu plan</h1>
                <p>Cambia de plan o cancela cuando quieras. El cobro es mensual.</p>
                @if (session('nivel'))
                    <a class="btn btn-ghost btn-sm" href="{{ url('/admin/planes') }}">Gestionar planes</a>
                @endif
            </header>

            @include('partials.alertas')

            @if (count($planes ?? []))
                <div class="planes-grid">
                    @foreach ($planes as $plan)
                        @include('partials.plan-card', ['plan' => $plan, 'suscripcion' => $suscripcion ?? null])
                    @endforeach
                </div>
            @else
                <p class="vacio">Aún no hay planes disponibles.</p>
            @endif
        </div>
    </section>
@endsection
