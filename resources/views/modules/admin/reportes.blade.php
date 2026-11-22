@extends('layouts.admin')

@section('titulo', 'Reportes · Aetherix Admin')
@section('titulo_pagina', 'Reportes')

@section('contenido')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="form-inline">
                <label for="desde" class="mr-2">Desde</label>
                <input type="date" class="form-control mr-3" id="desde" name="desde" value="{{ request('desde') }}">
                <label for="hasta" class="mr-2">Hasta</label>
                <input type="date" class="form-control mr-3" id="hasta" name="hasta" value="{{ request('hasta') }}">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card card-chart">
                <div class="card-header">
                    <h4 class="card-title">Ingresos por plan</h4>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="graficoIngresos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Rendimiento de planes</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table tablesorter">
                            <thead class="text-primary">
                                <tr>
                                    <th>Plan</th>
                                    <th>Suscriptores</th>
                                    <th>Ingresos</th>
                                    <th>Cancelaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rendimiento as $r)
                                    <tr>
                                        <td>{{ $r->plan }}</td>
                                        <td>{{ number_format($r->suscriptores) }}</td>
                                        <td>${{ number_format($r->ingresos, 2) }}</td>
                                        <td>{{ $r->cancelaciones }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">No hay datos para el período elegido.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        if (Chart.defaults.global) { Chart.defaults.global.defaultFontColor = '#9a9a9a'; }
        else { Chart.defaults.color = '#9a9a9a'; }

        new Chart(document.getElementById('graficoIngresos'), {
            type: 'bar',
            data: {
                labels: @json(collect($rendimiento)->pluck('plan')),
                datasets: [{
                    label: 'Ingresos ($)',
                    data: @json(collect($rendimiento)->pluck('ingresos')),
                    backgroundColor: '#a78bfa'
                }]
            }
        });
    </script>
@endsection