<?php

namespace App\Services\Reproductor\Proveedores;

use App\Models\Contenido;
use App\Models\Episodio;

interface ProveedorInterface
{
    public function obtenerNombre();

    public function obtenerFuentes(Contenido $contenido, ?Episodio $episodio = null);
}
