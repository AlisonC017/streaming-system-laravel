<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuditoriaController extends Controller
{
    /** Bitacora de auditoria: Total y Soporte la ven  */
    /** Bitacora de auditoria: la ven los tres niveles de administrador */
    public function index(Request $request)
    {
        $nivel = DB::table('administrador')->where('id_usuario', Auth::id())->value('nivel_acceso');
        abort_unless(in_array($nivel, ['Total', 'Soporte', 'Contenido'], true), 403);

        $q = DB::table('auditoria')->orderByDesc('fecha')->limit(200);
        if ($request->filled('tabla')) {
            $q->where('tabla', $request->tabla);
        }
        if ($request->filled('buscar')) {
            $q->where('descripcion', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('operacion')) {
            $q->where('operacion', $request->operacion);
        }

        try {
            $registros = $q->get();
        } catch (\Throwable $e) {
            report($e);
            $registros = collect();
            session()->now('error', 'No se pudo leer la auditoría. Revisa el permiso SELECT de rol_app sobre la tabla auditoria.');
        }

        return view('modules.admin.auditoria', ['registros' => $registros]);
    }
}
