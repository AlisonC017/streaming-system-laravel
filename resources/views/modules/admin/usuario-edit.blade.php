@extends('layouts.admin')

@section('titulo', 'Editar cliente · Aetherix Admin')
@section('titulo_pagina', 'Editar cliente')

@section('contenido')
    @include('modules.admin._mensajes')

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ $cliente->correo }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.usuarios.update', $cliente->id_usuario) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                value="{{ old('nombre', $cliente->nombre) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="apellidos">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos"
                                value="{{ old('apellidos', $cliente->apellidos) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="pais">País</label>
                            <input type="text" class="form-control" id="pais" name="pais"
                                value="{{ old('pais', $cliente->pais) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado de la cuenta</label>
                            <select class="form-control" id="estado" name="estado">
                                @foreach (['Activo', 'Inactivo'] as $e)
                                    <option value="{{ $e }}" @selected(old('estado', $cliente->estado) === $e)>{{ $e }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                        <a href="{{ route('admin.usuarios.show', $cliente->id_usuario) }}"
                            class="btn btn-secondary btn-sm">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
