@extends('layouts.admin')

@section('titulo', 'Cobros · Aetherix Admin')
@section('titulo_pagina', 'Cobros')

@section('contenido')
    @include('modules.admin._mensajes')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Cobro manual</h4>
                    <p class="card-category">Fuerza el cobro de una suscripción para demostrar el proceso.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.cobros.procesar') }}">
                        @csrf
                        <div class="form-group">
                            <label for="id_suscripcion">Suscripción</label>
                            <select class="form-control" id="id_suscripcion" name="id_suscripcion" required>
                                @foreach ($suscripciones as $s)
                                    <option value="{{ $s->id_suscripcion }}">{{ $s->cliente }} · {{ $s->plan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Procesar cobro</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Demostración de concurrencia</h4>
                    <p class="card-category">Dos cobros simultáneos sobre la misma suscripción.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.cobros.concurrencia') }}">
                        @csrf
                        <div class="form-group">
                            <label for="id_suscripcion_conc">Suscripción</label>
                            <select class="form-control" id="id_suscripcion_conc" name="id_suscripcion" required>
                                @foreach ($suscripciones as $s)
                                    <option value="{{ $s->id_suscripcion }}">{{ $s->cliente }} · {{ $s->plan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simular 2 cobros a la vez</button>
                    </form>

                    @isset($resultadoConcurrencia)
                        <ul class="list-unstyled mt-3 mb-0">
                            @foreach ($resultadoConcurrencia as $linea)
                                <li>{{ $linea }}</li>
                            @endforeach
                        </ul>
                    @endisset
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Cobros recientes</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table tablesorter">
                    <thead class="text-primary">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Plan</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cobros as $c)
                            <tr>
                                <td>{{ $c->id_pago }}</td>
                                <td>{{ $c->cliente }}</td>
                                <td>{{ $c->plan }}</td>
                                <td>${{ number_format($c->monto, 2) }}</td>
                                <td>{{ $c->fecha_pago }}</td>
                                <td><span
                                        class="badge badge-{{ match ($c->estado) {'Exitoso' => 'success','Pendiente' => 'warning','Fallido' => 'danger',default => 'info'} }}">{{ $c->estado }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Aún no hay cobros registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
