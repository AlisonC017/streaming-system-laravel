<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CuentaController extends Controller
{
    public function panel()
    {
        return view('modules.cuenta-cliente.panel', [
            'usuario' => Auth::user(),
            'suscripcion' => null, // TODO: conectar con sp_obtener_suscripcion_por_id
            'planes' => PlanController::listar(),
        ]);
    }

    //Historial pagos cliente
     public function historial()
    {
        try {
            $pagos = DB::select('EXEC sp_historial_pagos_cliente ?', [Auth::id()]);
        } catch (\Throwable $e) {
            report($e);
            $pagos = [];
        }

        return view('modules.cuenta-cliente.historial', ['pagos' => $pagos]);
    }
}
