<?php

namespace App\Services\Reproductor\Proveedores;

use App\Models\Contenido;
use App\Models\Episodio;
use App\Models\Temporada;

class SuperEmbedProvider implements ProveedorInterface
{
    use ResuelveEpisodioTmdb;
    public function obtenerNombre()
    {
        return 'superembed';
    }

    public function obtenerFuentes(Contenido $contenido, ?Episodio $episodio = null)
    {
        $fuentes = [];

        $id = $this->obtenerTmdbId($contenido);

        if ($id == '') {
            return $fuentes;
        }

        if ($contenido->tipo == 'pelicula') {
            $url = "https://multiembed.mov/?video_id={$id}&tmdb=1";

            $fuentes[] = $this->armarFuente($contenido, null, $url);
        } else {
            if (!$episodio) {
                return $fuentes;
            }

            $temporada = Temporada::find($episodio->temporada_id);

            if (!$temporada) {
                return $fuentes;
            }
            $posicion = $this->resolverPosicionTmdb($contenido, $temporada, $episodio);

            if (!$posicion) {
                return $fuentes;
            }

            [$numeroTemporada, $numeroEpisodio] = $posicion;
            $url = "https://multiembed.mov/?video_id={$id}&tmdb=1&s={$numeroTemporada}&e={$numeroEpisodio}";
            $fuentes[] = $this->armarFuente($contenido, $episodio, $url);
        }

        return $fuentes;
    }

    protected function armarFuente(Contenido $contenido, ?Episodio $episodio, string $url)
    {
        return [
            'contenido_id' => $contenido->_id,
            'episodio_id' => $episodio ? $episodio->_id : null,
            'proveedor' => $this->obtenerNombre(),
            'url' => $url,
            'tipo' => 'embed',
            'idioma' => 'multi',
            'subtitulos' => [],
            'estado' => 'pendiente',
            'prioridad' => 3,
            'calidad' => null,
            'firmada' => false,
            'huella' => sha1($this->obtenerNombre() . '|' . $url)
        ];
    }
}
