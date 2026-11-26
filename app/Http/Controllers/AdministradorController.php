<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdministradorController extends Controller
{
    public function index(Request $request)
    {
        $q = DB::table('administrador as a')->join('usuario as u', 'u.id_usuario', '=', 'a.id_usuario')
            ->select('u.id_usuario', 'u.nombre', 'u.apellidos', 'u.correo', 'u.estado', 'a.nivel_acceso')->orderBy('u.nombre');

        if ($request->filled('buscar')) {
            $b = '%' . $request->buscar . '%';
            $q->where(function ($w) use ($b) {
                $w->where('u.nombre', 'like', $b)
                    ->orWhere('u.apellidos', 'like', $b)
                    ->orWhere('u.correo', 'like', $b);
            });
        }

        if ($request->filled('nivel')) {
            $q->where('a.nivel_acceso', $request->nivel);
        }

        try {
            $administradores = $q->get();
        } catch (\Throwable $e) {
            report($e);
            $administradores = collect();
        }

        return view('modules.admin.administradores', ['administradores' => $administradores]);
    }
}
