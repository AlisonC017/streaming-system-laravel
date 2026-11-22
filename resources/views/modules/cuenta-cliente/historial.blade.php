@extends('layouts.cuenta')

@section('title', 'Historial de pagos · Aetherix')

@section('content')
    <h2>Historial de pagos</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Tarjeta</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagos as $p)
                <tr>
                    <td>{{ $p->fecha_pago }}</td>
                    <td>${{ $p->monto }}</td>
                    <td>{{ $p->ultimos4 ? '**** ' . $p->ultimos4 : 'Sin tarjeta' }}</td>
                    <td>{{ $p->estado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Aún no tienes pagos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
