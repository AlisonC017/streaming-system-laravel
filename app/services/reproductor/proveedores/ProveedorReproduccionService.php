<?php

namespace App\Services\Reproductor;

use Illuminate\Support\Facades\Http;

class ProveedorReproduccionService
{
    public function consultarPelicula($id)
    {
        $respuesta = Http::timeout(15)->get(
            config('services.reproduccion.url') . "/movies/{$id}"
        );

        if (!$respuesta->successful()) {
            return null;
        }

        return $respuesta->json();
    }

    public function consultarEpisodio($id, $temporada, $episodio)
    {
        $respuesta = Http::timeout(15)->get(
            config('services.reproduccion.url') . "/tv/{$id}/{$temporada}/{$episodio}"
        );

        if (!$respuesta->successful()) {
            return null;
        }

        return $respuesta->json();
    }
}
