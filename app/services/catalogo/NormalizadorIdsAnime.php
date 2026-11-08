<?php

namespace App\Services\Catalogo;

use App\Models\Contenido;
use App\Models\Episodio;
use App\Models\Identificador;

class NormalizadorIdsAnime
{
    // Paises de origen que se aceptan como anime
    protected $paisesAceptados = ['JP', 'CN', 'KR'];

    protected $tmdb;

    public function __construct(TMDBService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function buscarTmdbId(Contenido $contenido)
    {
        $anio = $this->anioDe($contenido);

        if (!$contenido->titulo || !$anio) {
            return null;
        }

        $serie = $this->mejorCandidato(
            $this->tmdb->buscarSeries($contenido->titulo),
            $anio
        );

        return $serie ? (int) $serie['id'] : null;
    }

    public function normalizar(Contenido $contenido)
    {
        $identificador = Identificador::where('contenido_id', $contenido->_id)->first();

        if ($identificador && $identificador->tmdb_id) {
            return (int) $identificador->tmdb_id;
        }

        $datos = [];

        if ($this->esTemporadaPosterior($contenido->titulo)) {
            $datos = $this->buscarTemporada($contenido);
        } else {
            $tmdbId = $this->buscarTmdbId($contenido);

            if ($tmdbId) {
                $datos = ['tmdb_id' => $tmdbId];
            }
        }

        if (empty($datos)) {
            return null;
        }

        Identificador::updateOrCreate(
            ['contenido_id' => $contenido->_id],
            $datos
        );

        return (int) $datos['tmdb_id'];
    }

    protected function buscarTemporada(Contenido $contenido)
    {
        $anio = $this->anioDe($contenido);
        $episodios = Episodio::where('contenido_id', $contenido->_id)->count();
        $enEmision = $contenido->estado == 'releasing';

        if (!$anio || !$episodios) {
            return [];
        }

        $serie = $this->mejorCandidato(
            $this->tmdb->buscarSeries($this->tituloBase($contenido->titulo))
        );

        if (!$serie) {
            return [];
        }

        $detalle = $this->tmdb->obtenerTemporadas($serie['id']);

        foreach ($detalle['seasons'] ?? [] as $temporada) {
            $numero = (int) ($temporada['season_number'] ?? 0);
            $anioTemporada = (int) substr($temporada['air_date'] ?? '', 0, 4);
            $cantidad = (int) ($temporada['episode_count'] ?? 0);
            $cantidadCalza = $cantidad == $episodios || ($enEmision && $episodios < $cantidad);

            if ($numero >= 1 && $anioTemporada == $anio && $cantidadCalza) {
                return [
                    'tmdb_id' => (int) $serie['id'],
                    'tmdb_temporada' => $numero
                ];
            }
        }

        return [];
    }

    // De los candidatos validos devuelve el de mas votos (descarta especiales y spin-offs)
    protected function mejorCandidato(array $series, $anio = null)
    {
        $mejor = null;

        foreach ($series as $serie) {
            if (!$this->coincide($serie, $anio)) {
                continue;
            }

            if ($mejor === null || ($serie['vote_count'] ?? 0) > ($mejor['vote_count'] ?? 0)) {
                $mejor = $serie;
            }
        }

        return $mejor;
    }

    protected function coincide(array $serie, $anio = null)
    {
        // 16 es animacion en TMDB
        $esAnimacion = in_array(16, $serie['genre_ids'] ?? []);
        $paises = $serie['origin_country'] ?? [];
        $esDePaisAceptado = count(array_intersect($paises, $this->paisesAceptados)) > 0;

        if (!$esAnimacion || !$esDePaisAceptado) {
            return false;
        }

        if ($anio === null) {
            return true;
        }

        $anioSerie = (int) substr($serie['first_air_date'] ?? '', 0, 4);
        return $anioSerie && abs($anioSerie - $anio) <= 1;
    }

    protected function anioDe(Contenido $contenido)
    {
        return (int) substr((string) $contenido->fecha_estreno, 0, 4);
    }

    protected function tituloBase($titulo)
    {
        $patron = '/\s+(Season\s*\d+|\d+(st|nd|rd|th)\s+Season|Final Season|Part\s*\d+|Cour\s*\d+|\d+(st|nd|rd|th)\s+Cour)\b.*$/i';
        return trim(preg_replace($patron, '', (string) $titulo));
    }

    protected function esTemporadaPosterior($titulo)
    {
        return $this->tituloBase($titulo) !== trim((string) $titulo);
    }
}
