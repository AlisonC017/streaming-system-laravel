<?php

namespace App\Services\Reproductor\Proveedores;

use App\Models\Contenido;
use App\Models\Episodio;
use App\Models\Identificador;
use App\Models\Temporada;
use App\Services\Catalogo\NormalizadorIdsAnime;
use App\Services\Catalogo\TMDBService;
use Illuminate\Support\Facades\Cache;

trait ResuelveEpisodioTmdb
{
    protected function obtenerTmdbId(Contenido $contenido)
    {
        $identificador = Identificador::where('contenido_id', $contenido->_id)->first();

        if ($identificador && $identificador->tmdb_id) {
            return (string) $identificador->tmdb_id;
        }

        if ($contenido->tmdb_id) {
            return (string) $contenido->tmdb_id;
        }

        if ($contenido->proveedor_catalogo == 'tmdb') {
            return (string) $contenido->proveedor_id;
        }

        return '';
    }

    // Devuelve [temporada, episodio] en la numeracion de TMDB, o null si no se
    // puede asegurar. Es preferible no tener fuente a tener una del episodio equivocado.
    protected function resolverPosicionTmdb(Contenido $contenido, Temporada $temporada, Episodio $episodio)
    {
        if ($contenido->tipo != 'anime') {
            return [(int) $temporada->numero, (int) $episodio->numero];
        }

        $id = $this->obtenerTmdbId($contenido);
        $identificador = Identificador::where('contenido_id', $contenido->_id)->first();
        $temporadaTmdb = $identificador && $identificador->tmdb_temporada
            ? (int) $identificador->tmdb_temporada
            : null;

        $temporadas = $this->obtenerTemporadasTmdb($id);

        if (empty($temporadas)) {
            return null;
        }

        if ($temporadaTmdb) {
            // El episodio tiene que existir dentro de esa temporada de TMDB
            foreach ($temporadas as $t) {
                if ($t['numero'] == $temporadaTmdb && $episodio->numero <= $t['episodios']) {
                    return [$temporadaTmdb, (int) $episodio->numero];
                }
            }

            return null;
        }

        // "Season 2", "Part 2"... sin temporada mapeada: nunca usar numeracion corrida
        if (app(NormalizadorIdsAnime::class)->esTemporadaPosterior($contenido->titulo)) {
            return null;
        }

        // Numeracion corrida (One Piece, Conan...)
        $numero = (int) $episodio->numero;
        $acumulado = 0;

        foreach ($temporadas as $t) {
            if ($numero <= $acumulado + $t['episodios']) {
                return [$t['numero'], $numero - $acumulado];
            }

            $acumulado += $t['episodios'];
        }

        return null;
    }

    protected function obtenerTemporadasTmdb($tmdbId)
    {
        return Cache::remember('tmdb_temporadas:' . $tmdbId, now()->addHours(12), function () use ($tmdbId) {
            $detalle = app(TMDBService::class)->obtenerTemporadas($tmdbId);

            if (!$detalle) {
                return null;
            }

            $lista = [];

            foreach ($detalle['seasons'] ?? [] as $t) {
                $numero = (int) ($t['season_number'] ?? 0);
                $cantidad = (int) ($t['episode_count'] ?? 0);

                if ($numero >= 1 && $cantidad > 0) {
                    $lista[] = ['numero' => $numero, 'episodios' => $cantidad];
                }
            }

            usort($lista, fn($a, $b) => $a['numero'] <=> $b['numero']);

            return $lista;
        });
    }
}
