<?php

namespace App\Services\Catalogo;

use Illuminate\Support\Facades\Http;
use App\Models\Contenido;
use App\Models\Identificador;

class TMDBService
{
    private $url = 'https://api.themoviedb.org/3';

    public function consultar($ruta, $parametros = [])
    {
        $respuesta = Http::timeout(15)->get(
            $this->url . $ruta,
            array_merge($parametros, [
                'api_key' => config('services.tmdb.key'),
                'language' => 'es-ES'
            ])
        );

        if ($respuesta->successful()) {
            return $respuesta->json();
        }

        return null;
    }

    public function buscarPeliculas($nombre)
    {
        $resultado = $this->consultar('/search/movie', [
            'query' => $nombre
        ]);

        return $resultado['results'] ?? [];
    }

    public function buscarSeries($nombre)
    {
        $resultado = $this->consultar('/search/tv', [
            'query' => $nombre
        ]);

        return $resultado['results'] ?? [];
    }

    public function buscarSerie($nombre)
    {
        $respuesta = $this->consultar('/search/tv', [
            'query' => $nombre
        ]);

        return $respuesta['results'][0] ?? null;
    }

    public function obtenerTemporadas($tmdbId)
    {
        return $this->consultar("/tv/{$tmdbId}");
    }

    public function obtenerEpisodios($tmdbId, $numeroTemporada)
    {
        return $this->consultar(
            "/tv/{$tmdbId}/season/{$numeroTemporada}"
        );
    }

    public function obtenerIdsExternos($tmdbId)
    {
        return $this->consultar("/tv/{$tmdbId}/external_ids");
    }

    public function importarContenido($id, $tipo)
    {
        if ($tipo == 'pelicula') {
            $ruta = '/movie/' . $id;
            $tipoContenido = 'pelicula';
        } elseif ($tipo == 'serie') {
            $ruta = '/tv/' . $id;
            $tipoContenido = 'serie';
        } else {
            return null;
        }

        $datos = $this->consultar($ruta);

        if (!$datos || !isset($datos['id'])) {
            return null;
        }

        $contenido = Contenido::updateOrCreate(
            [
                'proveedor_catalogo' => 'tmdb',
                'proveedor_id' => $datos['id'],
                'tipo' => $tipoContenido
            ],
            [
                'tmdb_id' => $datos['id'],
                'titulo' => $datos['title'] ?? $datos['name'] ?? 'Sin titulo',
                'tipo' => $tipoContenido,
                'descripcion' => $datos['overview'] ?? null,
                'imagen' => isset($datos['poster_path'])
                    ? 'https://image.tmdb.org/t/p/w500' . $datos['poster_path']
                    : null,
                'generos' => array_column($datos['genres'] ?? [], 'name'),
                'fecha_estreno' => $datos['release_date'] ?? $datos['first_air_date'] ?? null,
                'estado' => $datos['status'] ?? null,
                'puntuacion' => $datos['vote_average'] ?? null,
                'popularidad' => $datos['popularity'] ?? null
            ]
        );

        Identificador::updateOrCreate(
            [
                'contenido_id' => $contenido->_id
            ],
            [
                'tmdb_id' => $datos['id']
            ]
        );

        return $contenido;
    }

    public function obtenerPopulares($tipo, $pagina = 1)
    {
        if ($tipo == 'pelicula') {
            $ruta = '/movie/popular';
        } elseif ($tipo == 'serie') {
            $ruta = '/tv/popular';
        } else {
            return [];
        }

        $datos = $this->consultar($ruta, [
            'page' => $pagina
        ]);

        return $datos['results'] ?? [];
    }
}
