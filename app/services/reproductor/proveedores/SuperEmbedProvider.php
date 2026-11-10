<?php

namespace App\Services\Reproductor\Proveedores;

use App\Models\Contenido;
use App\Models\Episodio;
use App\Models\Identificador;
use App\Models\Temporada;

class SuperEmbedProvider implements ProveedorInterface
{
    public function obtenerNombre()
    {
        return 'superembed';
    }

    public function obtenerFuentes(Contenido $contenido, ?Episodio $episodio = null)
    {
        $fuentes = [];

        $identificador = Identificador::where('contenido_id', $contenido->_id)->first();

        $id = $identificador && $identificador->tmdb_id
            ? (string) $identificador->tmdb_id
            : '';

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

            $numeroTemporada = $temporada->numero;
            $numeroEpisodio = $episodio->numero;

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
