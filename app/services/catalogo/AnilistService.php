<?php

namespace App\Services\Catalogo;

use App\Models\Contenido;
use Illuminate\Support\Facades\Http;

class AniListService
{
    private $url = 'https://graphql.anilist.co';

    public function buscarAnime($nombre)
    {
        $query = '
            query ($search: String) {
                Page(page: 1, perPage: 20) {
                    media(search: $search, type: ANIME) {
                        id
                        title {
                            romaji
                            english
                            native
                        }
                        description(asHtml: false)
                        coverImage {
                            large
                        }
                        genres
                        status
                        episodes
                        averageScore
                        popularity
                    }
                }
            }
        ';

        $respuesta = Http::timeout(15)->post($this->url, [
            'query' => $query,
            'variables' => [
                'search' => $nombre
            ]
        ]);

        if ($respuesta->successful()) {
            return $respuesta->json()['data']['Page']['media'] ?? [];
        }

        return [];
    }

    public function obtenerAnime($id)
    {
        $query = '
            query ($id: Int) {
                Media(id: $id, type: ANIME) {
                    id
                    title {
                        romaji
                        english
                        native
                    }
                    description(asHtml: false)
                    coverImage {
                        large
                    }
                    genres
                    status
                    episodes
                    averageScore
                    popularity
                }
            }
        ';

        $respuesta = Http::timeout(15)->post($this->url, [
            'query' => $query,
            'variables' => [
                'id' => (int) $id
            ]
        ]);

        if ($respuesta->successful()) {
            return $respuesta->json()['data']['Media'] ?? null;
        }

        return null;
    }

    public function importarAnime($id)
    {
        $anime = $this->obtenerAnime($id);

        if (!$anime) {
            return null;
        }

        $titulo = $anime['title']['english']
            ?? $anime['title']['romaji']
            ?? $anime['title']['native']
            ?? 'Anime sin titulo';

        $contenido = Contenido::updateOrCreate(
            [
                'proveedor_catalogo' => 'anilist',
                'proveedor_id' => $anime['id']
            ],
            [
                'titulo' => $titulo,
                'tipo' => 'anime',
                'descripcion' => $anime['description'] ?? null,
                'imagen' => $anime['coverImage']['large'] ?? null,
                'generos' => $anime['genres'] ?? [],
                'estado' => strtolower($anime['status'] ?? ''),
                'puntuacion' => $anime['averageScore'] ?? null,
                'popularidad' => $anime['popularity'] ?? null
            ]
        );

        return $contenido;
    }

    public function obtenerPopulares($pagina = 1, $cantidad = 20)
    {
        $consulta = '
            query ($pagina: Int, $cantidad: Int) {
                Page(page: $pagina, perPage: $cantidad) {
                    media(type: ANIME, sort: POPULARITY_DESC, isAdult: false) {
                        id
                    }
                }
            }
        ';

        $respuesta = Http::timeout(15)->post($this->url, [
            'query' => $consulta,
            'variables' => [
                'pagina' => (int) $pagina,
                'cantidad' => (int) $cantidad
            ]
        ]);

        if ($respuesta->successful()) {
            return $respuesta->json()['data']['Page']['media'] ?? [];
        }

        return [];
    }
}
