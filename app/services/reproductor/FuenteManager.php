<?php

namespace App\Services\Reproductor;

use App\Models\Fuente;

class FuenteManager
{
    public function guardarFuente($datos)
    {
        $episodioId = $datos['episodio_id'] ?? null;

        $consulta = Fuente::where('contenido_id', $datos['contenido_id'])
            ->where('episodio_id', $episodioId)
            ->where('proveedor', $datos['proveedor']);

        // La huella distingue varias URLs del mismo proveedor y episodio.
        // Sin huella se usa la URL como identificador.
        if (!empty($datos['huella'])) {
            $consulta->where('huella', $datos['huella']);
        } else {
            $consulta->where('url', $datos['url']);
        }

        $fuente = $consulta->first();

        if (!$fuente && !empty($datos['huella'])) {
            $fuente = Fuente::where('contenido_id', $datos['contenido_id'])
                ->where('episodio_id', $episodioId)
                ->where('proveedor', $datos['proveedor'])
                ->where('url', $datos['url'])
                ->whereNull('huella')
                ->first();
        }

        if (!$fuente) {
            $fuente = new Fuente();
        }

        // Si es nueva o cambio la URL, se verifica de nuevo
        if (!$fuente->exists || $fuente->url !== $datos['url']) {
            $fuente->estado = 'pendiente';
        }

        $fuente->contenido_id = $datos['contenido_id'];
        $fuente->episodio_id = $episodioId;
        $fuente->proveedor = $datos['proveedor'];
        $fuente->url = $datos['url'];
        $fuente->tipo = $datos['tipo'];
        $fuente->idioma = $datos['idioma'] ?? 'original';
        $fuente->subtitulos = $datos['subtitulos'] ?? [];
        $fuente->prioridad = $datos['prioridad'] ?? 1;
        $fuente->huella = $datos['huella'] ?? $fuente->huella;
        $fuente->calidad = $datos['calidad'] ?? $fuente->calidad;
        $fuente->firmada = $datos['firmada'] ?? ($fuente->firmada ?? false);
        $fuente->fecha_expiracion = $datos['fecha_expiracion'] ?? null;

        $fuente->save();

        return $fuente;
    }

    public function obtenerFuentes($contenidoId, $episodioId = null)
    {
        return Fuente::where('contenido_id', $contenidoId)
            ->where('episodio_id', $episodioId)
            ->where('estado', 'disponible')
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')
                    ->orWhere('fecha_expiracion', '>', now());
            })
            ->orderBy('prioridad', 'asc')
            ->get();
    }

    public function eliminarFuente($id)
    {
        $fuente = Fuente::find($id);

        if (!$fuente) {
            return false;
        }

        return $fuente->delete();
    }
}
