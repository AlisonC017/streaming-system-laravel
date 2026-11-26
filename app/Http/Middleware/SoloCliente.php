<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Solo deja pasar a clientes (usuarios que estan en la tabla cliente).
 * Un administrador no tiene suscripcion ni tarjetas, se manda a su panel.
 */
class SoloCliente
{
    public function handle(Request $request, Closure $next): Response
    {
        $idUsuario = $request->user()->id_usuario;

        if (DB::table('administrador')->where('id_usuario', $idUsuario)->exists()) {
            return redirect()->route('admin')
                ->with('error', 'Esa sección es solo para clientes.');
        }

        if (! DB::table('cliente')->where('id_usuario', $idUsuario)->exists()) {
            abort(403);
        }

        return $next($request);
    }
}
