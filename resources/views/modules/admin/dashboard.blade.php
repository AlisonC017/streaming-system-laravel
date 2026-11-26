@extends('layouts.admin')

@section('titulo', 'Dashboard - Aetherix')
@section('titulo_pagina', 'Dashboard')

@section('contenido')
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card card-chart">
                <div class="card-header">
                    <h5 class="card-category">Suscripciones activas</h5>
                    <h3 class="card-title"><i class="tim-icons icon-single-02 text-primary"></i>
                        {{ number_format($suscripcionesActivas) }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-chart">
                <div class="card-header">
                    <h5 class="card-category">Ingresos del mes</h5>
                    <h3 class="card-title"><i class="tim-icons icon-coins text-primary"></i>
                        ₡{{ number_format($ingresosMes, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-chart">
                <div class="card-header">
                    <h5 class="card-category">Cobros fallidos</h5>
                    <h3 class="card-title"><i class="tim-icons icon-alert-circle-exc text-primary"></i>
                        {{ $cobrosFallidos }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-chart">
                <div class="card-header">
                    <h5 class="card-category">Cancelaciones</h5>
                    <h3 class="card-title"><i class="tim-icons icon-simple-remove text-primary"></i> {{ $cancelaciones }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Últimos cobros</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table tablesorter">
                    <thead class="text-primary">
                        <tr>
                            <th>Cliente</th>
                            <th>Plan</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ultimosCobros as $c)
                            <tr>
                                <td>{{ $c->cliente }}</td>
                                <td>{{ $c->plan }}</td>
                                <td>₡{{ number_format($c->monto, 2) }}</td>
                                <td><span
                                        class="badge badge-{{ match ($c->estado) { 'Exitoso' => 'success', 'Pendiente' => 'warning', 'Fallido' => 'danger', default => 'info'} }}">{{ $c->estado }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Aún no hay cobros registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection