@extends('layouts.admin')

@section('titulo', 'Auditoría - Aetherix')
@section('titulo_pagina', 'Bitácora de auditoría')

@section('contenido')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <form method="GET" class="form-inline">
                        <select name="tabla" class="form-control mr-2">
                            <option value="">Todas las tablas</option>
                            <option value="usuario">usuario</option>
                            <option value="suscripcion">suscripcion</option>
                            <option value="pago">pago</option>
                            <option value="metodo_pago">metodo_pago</option>
                            <option value="planes">planes</option>
                        </select>
                        <select name="operacion" class="form-control mr-2">
                            <option value="">Todas las operaciones</option>
                            <option value="INSERT">INSERT</option>
                            <option value="UPDATE">UPDATE</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                        <button class="btn btn-primary btn-sm">Filtrar</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table tablesorter">
                            <thead class="text-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Tabla</th>
                                    <th>Operación</th>
                                    <th>Descripción</th>
                                    <th>Usuario BD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($registros ?? [] as $r)
                                    <tr>
                                        <td>{{ $r->id_aud }}</td>
                                        <td>{{ $r->fecha }}</td>
                                        <td>{{ $r->tabla }}</td>
                                        <td>{{ $r->operacion }}</td>
                                        <td>{{ $r->descripcion }}</td>
                                        <td>{{ $r->usuario_bd }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Sin registros</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
