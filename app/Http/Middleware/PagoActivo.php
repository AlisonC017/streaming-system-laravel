<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PagoActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $idUsuario = $request->user()->id_usuario;

        $esAdmin = DB::table('administrador')->where('id_usuario', $idUsuario)->exists();

        $activa = DB::table('suscripcion')->where('id_usuario', $idUsuario)
            ->where('estado', 'Activa')
            ->exists();

        if (! $esAdmin && ! $activa) {
            return redirect()->route('inicio')->with('error', 'Necesitas una suscripción activa para ver el contenido');
        }

        return $next($request);
    }
}
