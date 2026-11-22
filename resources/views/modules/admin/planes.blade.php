@extends('layouts.admin')

@section('titulo', 'Planes - Aetherix')
@section('titulo_pagina', 'Gestión de Planes')

@section('contenido')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Planes Activos</h4>

                    @if (session('nivel') === 'Total')
                        <button class="btn btn-primary btn-sm">+ Nuevo Plan</button>
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
                                <tr>
                                    <td>Básico</td>
                                    <td>$7.99</td>
                                    <td>1080p</td>
                                    @if (session('nivel') === 'Total')
                                        <td class="text-right">
                                            <button class="btn btn-info btn-sm">Editar</button>
                                            <button class="btn btn-danger btn-sm">Descontinuar</button>
                                        </td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
