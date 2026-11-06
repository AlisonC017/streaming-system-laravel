<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloLocal
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->environment('local')) {
            abort(403);
        }

        return $next($request);
    }
}
