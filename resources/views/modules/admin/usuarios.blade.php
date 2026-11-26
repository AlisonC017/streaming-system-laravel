@extends('layouts.admin')

@section('titulo', 'Clientes · Aetherix Admin')
@section('titulo_pagina', 'Clientes')

@section('contenido')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="form-inline">
                <input type="search" class="form-control mr-3" name="buscar" placeholder="Nombre o correo"
                    value="{{ request('buscar') }}" aria-label="Buscar cliente">
                <select class="form-control mr-3" name="estado" aria-label="Estado">
                    <option value="">Todos los estados</option>
                    @foreach (['Activa', 'Pendiente', 'Suspendida'] as $e)
                        <option value="{{ $e }}" @selected(request('estado') === $e)>{{ $e }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Clientes registrados</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table tablesorter">
                    <thead class="text-primary">
                        <tr>
                            <th>Cliente</th>
                            <th>Correo</th>
                            <th>Plan</th>
                            <th>Suscripción</th>
                            <th>Registro</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clientes as $c)
                            <tr>
                                <td>{{ $c->nombre }} {{ $c->apellidos }}</td>
                                <td>{{ $c->correo }}</td>
                                <td>{{ $c->plan ?? '—' }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ match ($c->estado_suscripcion) {'Activa' => 'success','Pendiente' => 'warning','Suspendida' => 'danger',default => 'info'} }}">
                                        {{ $c->estado_suscripcion ?? 'Sin suscripción' }}
                                    </span>
                                </td>
                                <td>{{ $c->fecha_registro }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.usuarios.show', $c->id_usuario) }}"
                                        class="btn btn-sm btn-primary">Ver</a>
                                    @if ($c->estado === 'Activo')
                                        <form method="POST"
                                            action="{{ route('admin.usuarios.cancelar', $c->id_usuario) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('¿Cancelar la cuenta de {{ $c->nombre }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Cancelar cuenta</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No se encontraron clientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
