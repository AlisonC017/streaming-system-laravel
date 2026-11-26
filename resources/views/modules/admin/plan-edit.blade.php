@extends('layouts.admin')

@section('titulo', 'Editar plan · Aetherix Admin')
@section('titulo_pagina', 'Editar plan')

@section('contenido')
    @include('modules.admin._mensajes')

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.planes.update', $plan->id_plan) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nombre_plan">Nombre</label>
                            <input type="text" class="form-control" id="nombre_plan" name="nombre_plan"
                                value="{{ old('nombre_plan', $plan->nombre_plan) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="precio">Precio mensual</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="precio"
                                name="precio" value="{{ old('precio', $plan->precio) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="calidad">Resolución</label>
                            <select class="form-control" id="calidad" name="calidad">
                                @foreach (['SD', 'HD', 'FullHD', '4K'] as $c)
                                    <option value="{{ $c }}" @selected(old('calidad', $plan->calidad) === $c)>{{ $c }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                        <a href="{{ route('admin.planes') }}" class="btn btn-secondary btn-sm">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
