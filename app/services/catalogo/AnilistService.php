<?php

namespace App\Services\Catalogo;

use App\Models\Contenido;
use App\Models\Identificador;
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
                        idMal
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
                    idMal
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

        $identificador = Identificador::where(
            'anilist_id',
            $anime['id']
        )->first();

        if (!$identificador && !empty($anime['idMal'])) {
            $identificador = Identificador::where(
                'mal_id',
                $anime['idMal']
            )->first();
        }

        $contenido = null;

        if ($identificador) {
            $contenido = Contenido::find($identificador->contenido_id);
        }

        if (!$contenido) {
            $contenido = Contenido::where('proveedor_catalogo', 'anilist')
                ->where('proveedor_id', $anime['id'])
                ->first();
        }

        $titulo = $anime['title']['english']
            ?? $anime['title']['romaji']
            ?? $anime['title']['native']
            ?? 'Anime sin titulo';

        $datosContenido = [
            'id_mal' => $anime['idMal'] ?? null,
            'titulo' => $titulo,
            'tipo' => 'anime',
            'proveedor_catalogo' => 'anilist',
            'proveedor_id' => $anime['id'],
            'descripcion' => $anime['description'] ?? null,
            'imagen' => $anime['coverImage']['large'] ?? null,
            'generos' => $anime['genres'] ?? [],
            'estado' => strtolower($anime['status'] ?? ''),
            'puntuacion' => isset($anime['averageScore'])
                ? $anime['averageScore']
                : null,
            'popularidad' => $anime['popularity'] ?? null
        ];

        if ($contenido) {
            $contenido->update($datosContenido);
        } else {
            $contenido = Contenido::create($datosContenido);
        }

        Identificador::updateOrCreate(
            [
                'contenido_id' => $contenido->_id
            ],
            [
                'anilist_id' => $anime['id'],
                'mal_id' => $anime['idMal'] ?? null
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
            $datos = $respuesta->json();

            if (isset($datos['errors'])) {
                throw new \Exception(
                    'Error de AniList: ' . json_encode($datos['errors'])
                );
            }

            return $datos['data']['Page']['media'] ?? [];
        }

        throw new \Exception(
            'AniList respondio con HTTP ' . $respuesta->status() . ': ' . $respuesta->body()
        );
    }
}
