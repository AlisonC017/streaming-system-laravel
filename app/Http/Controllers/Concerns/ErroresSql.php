<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\QueryException;

trait ErroresSql
{
    /** Saca el mensaje del THROW del SP (ej. "La tarjeta esta vencida.") para mostrarlo en la vista */
    protected function mensajeSql(\Throwable $e): string
    {
        if ($e instanceof QueryException
            && preg_match('/\[SQL Server\](.*?)\s*\(Connection:/s', $e->getMessage(), $m)) {
            return trim($m[1]);
        }

        report($e);
        return 'No se pudo completar la operación.';
    }
}
