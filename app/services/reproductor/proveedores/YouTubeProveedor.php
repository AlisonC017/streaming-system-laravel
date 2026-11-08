<?php

namespace App\Services\Reproductor\Proveedores;

use App\Models\Contenido;
use App\Models\Episodio;
use App\Models\Identificador;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeProveedor implements ProveedorInterface
{
    public function obtenerNombre()
    {
        return 'youtube';
    }

    public function obtenerFuentes(Contenido $contenido, ?Episodio $episodio = null)
    {
        $id = $this->obtenerTmdbId($contenido);

        if ($id == '') {
            return [];
        }

        $tipo = $contenido->tipo == 'pelicula' ? 'movie' : 'tv';

        $clave = $this->obtenerClaveTrailer($tipo, $id);

        if (!$clave) {
            return [];
        }

        $url = "https://www.youtube-nocookie.com/embed/{$clave}";

        return [[
            'contenido_id' => $contenido->_id,
            'episodio_id' => $episodio ? $episodio->_id : null,
            'proveedor' => $this->obtenerNombre(),
            'url' => $url,
            'tipo' => 'embed',
            'idioma' => 'multi',
            'subtitulos' => [],
            'estado' => 'disponible',
            'prioridad' => 1,
            'calidad' => null,
            'firmada' => false,
            'huella' => sha1($this->obtenerNombre() . '|' . $url)
        ]];
    }

    // Clave del video de YouTube del trailer, en cache 24 horas
    protected function obtenerClaveTrailer($tipo, $id)
    {
        $cacheKey = "youtube_trailer:{$tipo}:{$id}";

        $guardada = Cache::get($cacheKey);

        if ($guardada) {
            return $guardada;
        }

        foreach (['es-ES', 'en-US'] as $idioma) {
            try {
                $respuesta = Http::timeout(10)->get(
                    "https://api.themoviedb.org/3/{$tipo}/{$id}/videos",
                    [
                        'api_key' => config('services.tmdb.key'),
                        'language' => $idioma
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('YouTubeProvider: fallo consultando TMDB', [
                    'mensaje' => $e->getMessage()
                ]);
                continue;
            }

            if (!$respuesta->successful()) {
                continue;
            }

            $candidatos = array_filter($respuesta->json('results') ?? [], function ($video) {
                return ($video['site'] ?? '') === 'YouTube' && !empty($video['key']);
            });

            // Oficiales primero
            usort($candidatos, function ($a, $b) {
                return (int) ($b['official'] ?? false) <=> (int) ($a['official'] ?? false);
            });

            foreach (['Trailer', 'Teaser'] as $tipoVideo) {
                foreach ($candidatos as $video) {
                    if (($video['type'] ?? '') === $tipoVideo) {
                        Cache::put($cacheKey, $video['key'], now()->addHours(24));
                        return $video['key'];
                    }
                }
            }
        }

        return null;
    }

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
}
