<?php

namespace App\Services\Catalogo;

use App\Models\Contenido;

class BuscadorService
{
    public function buscar($nombre)
    {
        if (!$nombre) {
            return [];
        }

        $contenidos = Contenido::where('titulo', 'regexp', "/" . preg_quote($nombre, '/') . "/i")
            ->limit(20)
            ->get();

        return $contenidos;
    }
}
