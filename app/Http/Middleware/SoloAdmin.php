<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // 'nivel' solo existe en la sesión de los administradores
        if (!session()->has('nivel')) {
            return redirect('/')
                ->with('error', 'No tenés permiso para entrar al panel de administración.');
        }

        return $next($request);
    }
}
