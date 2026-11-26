@extends('layouts.admin')

@section('titulo', 'Editar administrador · Aetherix Admin')
@section('titulo_pagina', 'Editar administrador')

@section('contenido')
    @include('modules.admin._mensajes')

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ $admin->nombre }} {{ $admin->apellidos }}</h4>
                    <p class="card-category">{{ $admin->correo }}</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.administradores.update', $admin->id_usuario) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nivel_acceso">Nivel de acceso</label>
                            <select class="form-control" id="nivel_acceso" name="nivel_acceso">
                                @foreach (['Total', 'Soporte', 'Contenido'] as $n)
                                    <option value="{{ $n }}" @selected(old('nivel_acceso', $admin->nivel_acceso) === $n)>{{ $n }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select class="form-control" id="estado" name="estado">
                                @foreach (['Activo', 'Inactivo'] as $e)
                                    <option value="{{ $e }}" @selected(old('estado', $admin->estado) === $e)>{{ $e }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                        <a href="{{ route('admin.administradores') }}" class="btn btn-secondary btn-sm">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
