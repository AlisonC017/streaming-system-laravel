@extends('layouts.admin')

@section('titulo', 'Cliente · Aetherix Admin')
@section('titulo_pagina', 'Detalle del cliente')

@section('contenido')
    @include('modules.admin._mensajes')

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ $cliente->nombre }} {{ $cliente->apellidos }}</h4>
                </div>
                <div class="card-body">
                    <p><strong>Correo:</strong> {{ $cliente->correo }}</p>
                    <p><strong>País:</strong> {{ $cliente->pais }}</p>
                    <p><strong>Nacimiento:</strong> {{ $cliente->fecha_nacimiento }}</p>
                    <p><strong>Estado de la cuenta:</strong> {{ $cliente->estado }}</p>
                    <p><strong>Registro:</strong> {{ $cliente->fecha_registro }}</p>
                    @if (session('nivel') === 'Total')
                        <a href="{{ route('admin.usuarios.edit', $cliente->id_usuario) }}" class="btn btn-info btn-sm">Editar</a>
                    @endif
                    <a href="{{ route('admin.usuarios') }}" class="btn btn-secondary btn-sm">Volver</a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Suscripción</h4>
                </div>
                <div class="card-body">
                    @if ($cliente->id_suscripcion)
                        <p><strong>Plan:</strong> {{ $cliente->plan }} ({{ $cliente->calidad }}) · ${{ number_format($cliente->precio, 2) }}</p>
                        <p><strong>Estado:</strong> {{ $cliente->estado_suscripcion }}</p>
                        <p><strong>Inicio:</strong> {{ $cliente->fecha_inicio }}</p>
                        <p><strong>Próximo cobro:</strong> {{ $cliente->fecha_proximo_cobro }}</p>
                        @if ($cliente->fecha_cancelacion)
                            <p><strong>Cancelada el:</strong> {{ $cliente->fecha_cancelacion }}</p>
                        @endif
                    @else
                        <p class="mb-0">Este cliente no tiene suscripción.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Métodos de pago</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table tablesorter">
                            <thead class="text-primary">
                                <tr><th>Tarjeta</th><th>Vence</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($metodos as $m)
                                    <tr>
                                        <td>**** {{ $m->ultimos4 }}</td>
                                        <td>{{ $m->fecha_vencimiento }}</td>
                                        <td>{{ $m->estado }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3">Sin métodos de pago.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Historial de pagos</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table tablesorter">
                            <thead class="text-primary">
                                <tr><th>#</th><th>Monto</th><th>Fecha</th><th>Tarjeta</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($pagos as $p)
                                    <tr>
                                        <td>{{ $p->id_pago }}</td>
                                        <td>${{ number_format($p->monto, 2) }}</td>
                                        <td>{{ $p->fecha_pago }}</td>
                                        <td>{{ $p->ultimos4 ? '**** ' . $p->ultimos4 : '—' }}</td>
                                        <td><span class="badge badge-{{ $p->estado === 'Exitoso' ? 'success' : 'danger' }}">{{ $p->estado }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5">Sin pagos registrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection