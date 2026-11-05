<?php

namespace App\Services\Reproductor;

use App\Models\Fuente;

class FuenteManager
{
    public function guardarFuente($datos)
    {
        $fuente = Fuente::where('contenido_id', $datos['contenido_id'])
            ->where('episodio_id', $datos['episodio_id'] ?? null)
            ->where('proveedor', $datos['proveedor'])
            ->first();

        if (!$fuente) {
            $fuente = new Fuente();
        }

        $fuente->contenido_id = $datos['contenido_id'];
        $fuente->episodio_id = $datos['episodio_id'] ?? null;
        $fuente->proveedor = $datos['proveedor'];
        $fuente->url = $datos['url'];
        $fuente->tipo = $datos['tipo'];
        $fuente->idioma = $datos['idioma'] ?? 'original';
        $fuente->estado = 'pendiente';
        $fuente->prioridad = $datos['prioridad'] ?? 1;

        $fuente->save();

        return $fuente;
    }

    public function obtenerFuentes($contenidoId, $episodioId = null)
    {
        return Fuente::where('contenido_id', $contenidoId)
            ->where('episodio_id', $episodioId)
            ->where('estado', 'disponible')
            ->orderBy('prioridad', 'asc')
            ->get();
    }

    public function eliminarFuente($id)
    {
        $fuente = Fuente::find($id);

        if ($fuente) {
            $fuente->delete();
            return true;
        }

        return false;
    }
}
