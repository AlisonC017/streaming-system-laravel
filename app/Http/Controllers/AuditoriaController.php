<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuditoriaController extends Controller
{
    /** Bitacora de auditoria: Total y Soporte la ven  */
    public function index(Request $request)
    {
        $nivel = DB::table('administrador')->where('id_usuario', Auth::id())->value('nivel_acceso');
        abort_unless(in_array($nivel, ['Total', 'Soporte', 'Contenido'], true), 403);

        $q = DB::table('auditoria')->orderByDesc('fecha')->limit(200);
        if ($request->filled('tabla')) {
            $q->where('tabla', $request->tabla);
        }
        if ($request->filled('operacion')) {
            $q->where('operacion', $request->operacion);
        }

        try {
            $registros = $q->get();
        } catch (\Throwable $e) {
            report($e);
            $registros = collect();
        }

        return view('modules.admin.auditoria', ['registros' => $registros]);
    }
}
