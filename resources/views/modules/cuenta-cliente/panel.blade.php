@extends('layouts.cuenta')

@section('title', 'Mi panel · Aetherix')

@section('contenido')
    @include('partials.alertas')

    @if (!$suscripcion)
        <div class="card p-3 mb-4">
            <h2>Aún no tienes una suscripción</h2>
            <p>Elige un plan para empezar a ver todo el catálogo.</p>
        </div>
    @else
        <div class="card p-3 mb-4">
            <h2>Estás suscrito a un plan</h2>
            <p>Estado: {{ $suscripcion->estado }} · Próximo cobro: {{ $suscripcion->fecha_proximo_cobro }}</p>
        </div>
    @endif

    <div class="row">
        @foreach ($planes as $plan)
            <div class="col-md-3 mb-3">
                <div class="card p-3">
                    <h3>{{ $plan->nombre_plan }}</h3>
                    <p>${{ $plan->precio }} / mes · {{ $plan->calidad }}</p>
                    @if ($suscripcion && $suscripcion->id_plan == $plan->id_plan)
                        <span>Tu plan</span>
                    @else
                        <a class="btn btn-primary"
                            href="#">{{ $suscripcion ? 'Cambiar a este plan' : 'Suscribirse' }}</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
