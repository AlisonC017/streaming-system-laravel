<?php

namespace App\Services\Reproductor\Proveedores;

use App\Models\Contenido;
use App\Models\Episodio;
use App\Models\Identificador;
use App\Models\Temporada;

class VidSrcProvider implements ProveedorInterface
{
    public function obtenerNombre()
    {
        return 'vidsrc';
    }

    public function obtenerFuentes(Contenido $contenido, ?Episodio $episodio = null)
    {
        $fuentes = [];

        $id = $this->obtenerTmdbId($contenido);

        if ($id == '') {
            return $fuentes;
        }

        if ($contenido->tipo == 'pelicula') {

            $url = "https://vidsrc.sh/embed/movie/{$id}";

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

            if (!$numeroTemporada || !$numeroEpisodio) {
                return $fuentes;
            }

            $url = "https://vidsrc.sh/embed/tv/{$id}/{$numeroTemporada}/{$numeroEpisodio}";

            $fuentes[] = $this->armarFuente($contenido, $episodio, $url);
        }

        return $fuentes;
    }

    protected function obtenerTmdbId(Contenido $contenido)
    {
        $identificador = Identificador::where('contenido_id', $contenido->_id)->first();

        if ($identificador && $identificador->tmdb_id) {
            return (string) $identificador->tmdb_id;
        }

        // Compatibilidad con registros anteriores
        if ($contenido->tmdb_id) {
            return (string) $contenido->tmdb_id;
        }

        if ($contenido->proveedor_catalogo == 'tmdb') {
            return (string) $contenido->proveedor_id;
        }

        return '';
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
            'prioridad' => 1,
            'calidad' => null,
            'firmada' => false,
            'huella' => sha1($this->obtenerNombre() . '|' . $url)
        ];
    }
}
