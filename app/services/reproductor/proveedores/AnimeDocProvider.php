<?php

namespace App\Services\Reproductor\Proveedores;

use App\Models\Contenido;
use App\Models\Episodio;
use App\Models\Identificador;
use App\Models\Temporada;
use App\Services\Catalogo\AnimeDocService;

class AnimeDocProvider implements ProveedorInterface
{
    protected $animeDoc;

    public function __construct(AnimeDocService $animeDoc)
    {
        $this->animeDoc = $animeDoc;
    }

    public function obtenerNombre()
    {
        return 'animedoc';
    }

    public function obtenerFuentes(Contenido $contenido, ?Episodio $episodio = null)
    {
        $fuentes = [];

        $identificador = Identificador::where('contenido_id', $contenido->_id)->first();

        if (!$identificador) {
            return $fuentes;
        }

        $subjectId = $identificador->animedoc_subject_id;
        $detailPath = $identificador->animedoc_detail_path;

        if (!$subjectId || !$detailPath) {
            return $fuentes;
        }

        $temporada = 0;
        $numeroEpisodio = 0;

        if ($contenido->tipo != 'pelicula') {

            if (!$episodio) {
                return $fuentes;
            }

            $temporadaModelo = Temporada::find($episodio->temporada_id);

            if (!$temporadaModelo) {
                return $fuentes;
            }

            $temporada = $temporadaModelo->numero;
            $numeroEpisodio = $episodio->numero;
        }

        $datos = $this->animeDoc->obtenerFuentes(
            $subjectId,
            $detailPath,
            $temporada,
            $numeroEpisodio
        );

        $descargas = $datos['downloads'] ?? [];
        $subtitulos = $datos['captions'] ?? [];

        foreach ($descargas as $descarga) {

            if (empty($descarga['url']) || ($descarga['vipLocked'] ?? false)) {
                continue;
            }

            $url = $descarga['url'];
            $calidad = $descarga['resolution'] ?? null;
            $firmada = parse_url($url, PHP_URL_QUERY) !== null;

            $fuentes[] = [
                'contenido_id' => $contenido->_id,
                'episodio_id' => $episodio ? $episodio->_id : null,
                'proveedor' => $this->obtenerNombre(),
                'url' => $url,
                'tipo' => 'mp4',
                'idioma' => 'original',
                'subtitulos' => $subtitulos,
                'estado' => 'pendiente',
                'prioridad' => $calidad !== null ? (int) (1080 - (int) $calidad) : 1,
                'calidad' => $calidad,
                'firmada' => $firmada,
                'fecha_expiracion' => $firmada
                    ? now()->addMinutes((int) config('services.animedoc.ttl_minutos', 30))
                    : null,
                'huella' => $this->calcularHuella($url, $calidad)
            ];
        }

        return $fuentes;
    }

    protected function calcularHuella(string $url, $calidad)
    {
        $base = strtok($url, '?#');

        return sha1($this->obtenerNombre() . '|' . $calidad . '|' . $base);
    }
}
