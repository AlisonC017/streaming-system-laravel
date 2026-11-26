<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminClientesController extends Controller
{
    /** Clientes con su plan y estado de suscripcion */
    public function index(Request $request)
    {
        $q = DB::table('cliente as c')
            ->join('usuario as u', 'u.id_usuario', '=', 'c.id_usuario')
            ->leftJoin('suscripcion as s', 's.id_usuario', '=', 'c.id_usuario')
            ->leftJoin('planes as p', 'p.id_plan', '=', 's.id_plan')
            ->select(
                'u.id_usuario',
                DB::raw("CONCAT(u.nombre, ' ', u.apellidos) as nombre"),
                'u.correo',
                DB::raw("COALESCE(p.nombre_plan, 'Sin plan') as [plan]"),
                DB::raw("COALESCE(s.estado, 'Sin suscripcion') as estado"),
                's.fecha_inicio as fecha_registro'
            )
            ->orderBy('u.nombre');

        if ($request->filled('buscar')) {
            $b = '%' . $request->buscar . '%';
            $q->where(function ($w) use ($b) {
                $w->where('u.nombre', 'like', $b)
                    ->orWhere('u.apellidos', 'like', $b)
                    ->orWhere('u.correo', 'like', $b);
            });
        }

        if ($request->filled('estado')) {
            $q->where('s.estado', $request->estado);
        }

        try {
            $clientes = $q->get();
        } catch (\Throwable $e) {
            report($e);
            $clientes = collect();
        }

        return view('modules.admin.usuarios', ['clientes' => $clientes]);
    }
}
