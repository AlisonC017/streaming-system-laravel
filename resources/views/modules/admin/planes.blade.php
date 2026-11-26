@extends('layouts.admin')

@section('titulo', 'Planes - Aetherix')
@section('titulo_pagina', 'Gestión de Planes')

@section('contenido')
    @include('modules.admin._mensajes')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Planes Activos</h4>

                    @if (session('nivel') === 'Total')
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPlan">+ Nuevo
                            Plan</button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table tablesorter">
                            <thead class="text-primary">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Resolución</th>
                                    @if (session('nivel') === 'Total')
                                        <th class="text-right">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($planes as $plan)
                                    <tr>
                                        <td>{{ $plan->nombre_plan }}</td>
                                        <td>₡{{ number_format($plan->precio, 2) }}</td>
                                        <td>{{ $plan->calidad }}</td>
                                        @if (session('nivel') === 'Total')
                                            <td class="text-right">
                                                <a href="{{ route('admin.planes.edit', $plan->id_plan) }}"
                                                    class="btn btn-info btn-sm">Editar</a>
                                                <form method="POST"
                                                    action="{{ route('admin.planes.destroy', $plan->id_plan) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('¿Descontinuar {{ $plan->nombre_plan }}? Sus suscripciones pasarán al plan elegido.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <select name="id_plan_destino"
                                                        class="form-control form-control-sm d-inline w-auto" required>
                                                        <option value="">Migrar a...</option>
                                                        @foreach ($planes as $otro)
                                                            @if ($otro->id_plan != $plan->id_plan)
                                                                <option value="{{ $otro->id_plan }}">
                                                                    {{ $otro->nombre_plan }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <button type="submit"
                                                        class="btn btn-danger btn-sm">Descontinuar</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ session('nivel') === 'Total' ? 4 : 3 }}">Aún no hay planes
                                            registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('nivel') === 'Total')
        <div class="modal fade" id="modalPlan" tabindex="-1" role="dialog" aria-labelledby="modalPlanTitulo"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.planes.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalPlanTitulo">Nuevo plan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <i class="tim-icons icon-simple-remove"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="nombre_plan">Nombre</label>
                                <input type="text" class="form-control" id="nombre_plan" name="nombre_plan" required>
                            </div>
                            <div class="form-group">
                                <label for="precio">Precio mensual ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="precio"
                                    name="precio" required>
                            </div>
                            <div class="form-group">
                                <label for="calidad">Resolución</label>
                                <select class="form-control" id="calidad" name="calidad">
                                    <option>1080p</option>
                                    <option>4K</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm">Guardar plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
