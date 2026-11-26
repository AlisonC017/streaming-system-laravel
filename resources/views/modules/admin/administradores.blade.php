@extends('layouts.admin')

@section('titulo', 'Administradores · Aetherix Admin')
@section('titulo_pagina', 'Administradores')

@section('contenido')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="form-inline">
                <input type="search" class="form-control mr-3" name="buscar" placeholder="Nombre o correo"
                    value="{{ request('buscar') }}" aria-label="Buscar administrador">
                <select class="form-control mr-3" name="nivel" aria-label="Nivel de acceso">
                    <option value="">Todos los niveles</option>
                    @foreach (['Total', 'Soporte', 'Contenido'] as $n)
                        <option value="{{ $n }}" @selected(request('nivel') === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Administradores</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table tablesorter">
                    <thead class="text-primary">
                        <tr>
                            <th>Administrador</th>
                            <th>Correo</th>
                            <th>Nivel de acceso</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($administradores as $a)
                            <tr>
                                <td>{{ $a->nombre }} {{ $a->apellidos }}</td>
                                <td>{{ $a->correo }}</td>
                                <td><span
                                        class="badge badge-{{ match ($a->nivel_acceso) {'Total' => 'success','Soporte' => 'info','Contenido' => 'warning',default => 'secondary'} }}">{{ $a->nivel_acceso }}</span>
                                </td>
                                <td>{{ $a->estado }}</td>
                                <td class="text-right">
                                    @if (session('nivel') === 'Total')
                                        <a href="{{ route('admin.administradores.edit', $a->id_usuario) }}"
                                            class="btn btn-sm btn-info">Editar</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No se encontraron administradores.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
