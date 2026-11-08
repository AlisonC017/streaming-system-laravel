<?php

namespace App\Services\Reproductor\Proveedores;

use App\Models\Contenido;
use App\Models\Episodio;
use App\Models\Identificador;
use App\Models\Temporada;
use App\Services\Catalogo\TMDBService;
use Illuminate\Support\Facades\Cache;

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

            $temporadaTmdb = $this->obtenerTemporadaTmdb($contenido);

            if ($temporadaTmdb) {
                // Anime que es "Season 2", etc, la temporada ya esta guardada
                $numeroTemporada = $temporadaTmdb;
                $numeroEpisodio = $episodio->numero;
            } elseif ($contenido->tipo == 'anime') {
                // Anime con numeracion corrida (One Piece, Naruto...): se convierte
                // al formato temporada/episodio de TMDB
                $posicion = $this->resolverEpisodioAnime($id, $episodio->numero);

                if (!$posicion) {
                    return $fuentes;
                }

                $numeroTemporada = $posicion[0];
                $numeroEpisodio = $posicion[1];
            } else {
                $numeroTemporada = $temporada->numero;
                $numeroEpisodio = $episodio->numero;
            }

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

    protected function obtenerTemporadaTmdb(Contenido $contenido)
    {
        $identificador = Identificador::where('contenido_id', $contenido->_id)->first();

        if ($identificador && $identificador->tmdb_temporada) {
            return (int) $identificador->tmdb_temporada;
        }

        return null;
    }

    // Convierte el numero corrido de un anime (ej. 1100) en [temporada, episodio] de TMDB
    // sumando los episodios de cada temporada. Si TMDB todavia no tiene ese episodio
    // (o no responde) devuelve null y no se registra fuente, para no dejar enlaces rotos
    protected function resolverEpisodioAnime($tmdbId, $numero)
    {
        $numero = (int) $numero;

        if ($numero < 1) {
            return null;
        }

        $temporadas = $this->obtenerTemporadasTmdb($tmdbId);

        if (empty($temporadas)) {
            return null;
        }

        $acumulado = 0;

        foreach ($temporadas as $temporada) {
            if ($numero <= $acumulado + $temporada['episodios']) {
                return [$temporada['numero'], $numero - $acumulado];
            }

            $acumulado += $temporada['episodios'];
        }

        return null;
    }

    // Temporadas de la serie en TMDB, en cache 12 horas
    // para no consultar a TMDB por cada episodio
    protected function obtenerTemporadasTmdb($tmdbId)
    {
        return Cache::remember('tmdb_temporadas:' . $tmdbId, now()->addHours(12), function () use ($tmdbId) {
            $detalle = app(TMDBService::class)->obtenerTemporadas($tmdbId);

            // Si TMDB falla se devuelve null y no se guarda en cache
            if (!$detalle) {
                return null;
            }

            $lista = [];

            foreach ($detalle['seasons'] ?? [] as $temporada) {
                $numero = (int) ($temporada['season_number'] ?? 0);
                $cantidad = (int) ($temporada['episode_count'] ?? 0);

                if ($numero >= 1 && $cantidad > 0) {
                    $lista[] = ['numero' => $numero, 'episodios' => $cantidad];
                }
            }

            usort($lista, function ($a, $b) {
                return $a['numero'] <=> $b['numero'];
            });

            return $lista;
        });
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
