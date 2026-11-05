<?php

namespace App\Services\Catalogo;

use Illuminate\Support\Facades\Http;
use App\Models\Contenido;
use App\Models\Temporada;
use App\Models\Episodio;

class TVmazeService
{
    private $url = 'https://api.tvmaze.com';

    private function consultar($ruta, $parametros = [])
    {
        $respuesta = Http::timeout(15)
            ->withUserAgent('StreamingSystem/1.0')
            ->get($this->url . $ruta, $parametros);

        if ($respuesta->successful()) {
            return $respuesta->json();
        }

        return null;
    }

    public function buscarSeries($nombre)
    {
        return $this->consultar('/search/shows', [
            'q' => $nombre
        ]) ?? [];
    }

    public function obtenerSerie($id)
    {
        return $this->consultar('/shows/' . $id);
    }

    public function importarTemporadasYEpisodios($contenidoId, $tvmazeId)
    {
        $contenido = Contenido::find($contenidoId);

        if (!$contenido || $contenido->tipo != 'serie') {
            return null;
        }

        $serie = $this->obtenerSerie($tvmazeId);

        if (!$serie) {
            return null;
        }

        $temporadasApi = $this->consultar('/shows/' . $tvmazeId . '/seasons');

        $episodiosApi = $this->consultar('/shows/' . $tvmazeId . '/episodes', [
            'specials' => 1
        ]);

        if ($temporadasApi === null || $episodiosApi === null) {
            return null;
        }

        $temporadasGuardadas = [];
        $totalEpisodios = 0;

        foreach ($temporadasApi as $datosTemporada) {

            $numero = $datosTemporada['number'] ?? 0;

            $temporada = Temporada::updateOrCreate(
                [
                    'contenido_id' => $contenido->_id,
                    'proveedor_id' => $datosTemporada['id']
                ],
                [
                    'numero' => $numero,
                    'titulo' => $datosTemporada['name'] ?? 'Temporada ' . $numero,
                    'descripcion' => null,
                    'fecha_estreno' => $datosTemporada['premiereDate'] ?? null,
                    'cantidad_episodios' => 0
                ]
            );

            $temporadasGuardadas[$numero] = $temporada;
        }

        foreach ($episodiosApi as $datosEpisodio) {

            $numeroTemporada = $datosEpisodio['season'] ?? 0;

            if (!isset($temporadasGuardadas[$numeroTemporada])) {
                continue;
            }

            $temporada = $temporadasGuardadas[$numeroTemporada];

            Episodio::updateOrCreate(
                [
                    'contenido_id' => $contenido->_id,
                    'proveedor_id' => $datosEpisodio['id']
                ],
                [
                    'temporada_id' => $temporada->_id,
                    'numero' => $datosEpisodio['number'] ?? null,
                    'titulo' => $datosEpisodio['name'] ?? 'Sin titulo',
                    'descripcion' => isset($datosEpisodio['summary'])
                        ? strip_tags($datosEpisodio['summary'])
                        : null,
                    'imagen' => $datosEpisodio['image']['original']
                        ?? $datosEpisodio['image']['medium']
                        ?? null,
                    'duracion' => $datosEpisodio['runtime'] ?? null,
                    'fecha_estreno' => $datosEpisodio['airdate'] ?? null
                ]
            );

            $totalEpisodios++;
        }

        foreach ($temporadasGuardadas as $temporada) {

            $cantidad = Episodio::where('temporada_id', $temporada->_id)->count();

            $temporada->update([
                'cantidad_episodios' => $cantidad
            ]);
        }

        return [
            'serie' => $serie['name'] ?? $contenido->titulo,
            'temporadas' => count($temporadasGuardadas),
            'episodios' => $totalEpisodios
        ];
    }
}
