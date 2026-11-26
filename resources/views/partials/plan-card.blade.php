
{{-- Tarjeta de un plan
     Uso: @include('partials.plan-card', ['plan' => $plan, 'suscripcion' => $suscripcion ?? null])
     $plan: id_plan, nombre_plan, precio, calidad
     $suscripcion (opcional): id_plan, estado --}}
@php
    $suscripcion = $suscripcion ?? null;
    $vigente = $suscripcion && $suscripcion->estado !== 'Cancelada';
    $esActual = $vigente && $suscripcion->id_plan == $plan->id_plan;
    $porPagar = $esActual && $suscripcion->estado !== 'Activa'; // Pendiente o Suspendida
    $esAdmin = (bool) session('nivel');
    $urlCheckout = Route::has('suscripcion.checkout')
        ? route('suscripcion.checkout', $plan->id_plan)
        : url('/suscripcion/checkout/' . $plan->id_plan);
@endphp

<article @class(['plan-card', 'is-actual' => $esActual])>
    <div @class(['plan-card__head'])>
        <h3>{{ $plan->nombre_plan }}</h3>
        <span @class(['chip'])>{{ $plan->calidad }}</span>
    </div>

    <div @class(['plan-card__precio'])>
        ₡{{ number_format($plan->precio, 2) }} <small>/ mes</small>
    </div>

    @if ($esAdmin)
        {{-- un administrador solo ve los planes, no se suscribe --}}
    @elseif ($porPagar)
        <span @class(['chip', 'chip--gold'])>Tu plan · {{ $suscripcion->estado }}</span>
        <a @class(['btn', 'btn-primary', 'btn-sm']) href="{{ $urlCheckout }}">Pagar ahora</a>
    @elseif ($esActual)
        <span @class(['chip', 'chip--gold'])>Tu plan actual</span>
        <a @class(['btn', 'btn-ghost', 'btn-sm']) aria-disabled="true">Plan actual</a>
    @elseif (!auth()->check())
        <a @class(['btn', 'btn-primary', 'btn-sm']) href="{{ route('register') }}">Crear cuenta y suscribirme</a>
    @elseif ($vigente)
        <a @class(['btn', 'btn-ghost', 'btn-sm']) href="{{ $urlCheckout }}">Cambiar a este plan</a>
    @else
        <a @class(['btn', 'btn-primary', 'btn-sm']) href="{{ $urlCheckout }}">Suscribirme</a>
    @endif
</article>
